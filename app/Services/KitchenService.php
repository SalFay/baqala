<?php

namespace App\Services;

use App\Models\KitchenOrder;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Collection;

class KitchenService
{
    /**
     * Create kitchen orders for an order
     * Splits order items by station
     */
    public function createKitchenOrder(Order $order): Collection
    {
        $kitchenOrders = collect();

        foreach ($order->items as $item) {
            // Skip items that don't need kitchen preparation
            if (!$this->requiresKitchenPrep($item)) {
                continue;
            }

            $kitchenOrder = KitchenOrder::create([
                'store_id' => $order->store_id,
                'order_id' => $order->id,
                'order_item_id' => $item->id,
                'status' => KitchenOrder::STATUS_PENDING,
                'station' => $this->determineStation($item),
                'priority' => $this->determinePriority($order),
                'notes' => $item->notes,
                'estimated_time' => $this->estimateTime($item),
            ]);

            $kitchenOrders->push($kitchenOrder);
        }

        return $kitchenOrders;
    }

    /**
     * Update kitchen order status
     */
    public function updateOrderStatus(int $kitchenOrderId, string $status): KitchenOrder
    {
        $kitchenOrder = KitchenOrder::findOrFail($kitchenOrderId);

        switch ($status) {
            case KitchenOrder::STATUS_PREPARING:
                $kitchenOrder->startPreparing();
                break;
            case KitchenOrder::STATUS_READY:
                $kitchenOrder->markReady();
                break;
            case KitchenOrder::STATUS_SERVED:
                $kitchenOrder->markServed();
                break;
            case KitchenOrder::STATUS_CANCELLED:
                $kitchenOrder->cancel();
                break;
        }

        return $kitchenOrder->fresh();
    }

    /**
     * Get orders by station
     */
    public function getOrdersByStation(?string $station = null): Collection
    {
        $query = KitchenOrder::with(['order', 'orderItem.product'])
            ->active()
            ->prioritized();

        if ($station) {
            $query->forStation($station);
        }

        return $query->get();
    }

    /**
     * Get pending orders for KDS display
     */
    public function getPendingOrders(?string $station = null): Collection
    {
        $query = KitchenOrder::with(['order.customer', 'order.restaurantTable', 'orderItem.product'])
            ->whereIn('status', [KitchenOrder::STATUS_PENDING, KitchenOrder::STATUS_PREPARING])
            ->prioritized();

        if ($station) {
            $query->forStation($station);
        }

        return $query->get()->groupBy('order_id');
    }

    /**
     * Get ready orders waiting to be served
     */
    public function getReadyOrders(): Collection
    {
        return KitchenOrder::with(['order.customer', 'order.restaurantTable', 'orderItem.product'])
            ->ready()
            ->orderBy('completed_at')
            ->get()
            ->groupBy('order_id');
    }

    /**
     * Mark all items for an order as ready
     */
    public function markOrderReady(int $orderId): int
    {
        $count = 0;
        $kitchenOrders = KitchenOrder::where('order_id', $orderId)->active()->get();

        foreach ($kitchenOrders as $kitchenOrder) {
            if ($kitchenOrder->markReady()) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Mark item as ready
     */
    public function markAsReady(int $kitchenOrderId): KitchenOrder
    {
        return $this->updateOrderStatus($kitchenOrderId, KitchenOrder::STATUS_READY);
    }

    /**
     * Get kitchen statistics
     */
    public function getStatistics(): array
    {
        $today = today();

        return [
            'pending' => KitchenOrder::pending()->whereDate('created_at', $today)->count(),
            'preparing' => KitchenOrder::preparing()->whereDate('created_at', $today)->count(),
            'ready' => KitchenOrder::ready()->whereDate('created_at', $today)->count(),
            'completed_today' => KitchenOrder::where('status', KitchenOrder::STATUS_SERVED)
                ->whereDate('completed_at', $today)->count(),
            'avg_prep_time' => $this->getAveragePrepTime(),
        ];
    }

    /**
     * Get average preparation time in minutes
     */
    protected function getAveragePrepTime(): ?float
    {
        $orders = KitchenOrder::whereNotNull('started_at')
            ->whereNotNull('completed_at')
            ->whereDate('created_at', today())
            ->get();

        if ($orders->isEmpty()) {
            return null;
        }

        $totalMinutes = $orders->sum(fn($o) => $o->started_at->diffInMinutes($o->completed_at));
        return round($totalMinutes / $orders->count(), 1);
    }

    /**
     * Check if item requires kitchen preparation
     */
    protected function requiresKitchenPrep(OrderItem $item): bool
    {
        // Check product category or type
        $product = $item->product;
        if (!$product) {
            return false;
        }

        // Items with certain categories/flags need kitchen prep
        // This can be customized based on business logic
        return $product->requires_kitchen_prep ?? true;
    }

    /**
     * Determine which station should prepare the item
     */
    protected function determineStation(OrderItem $item): ?string
    {
        $product = $item->product;
        if (!$product) {
            return null;
        }

        // Get station from product or category
        return $product->kitchen_station
            ?? $product->category?->kitchen_station
            ?? null;
    }

    /**
     * Determine priority based on order
     */
    protected function determinePriority(Order $order): string
    {
        // VIP customers
        if ($order->customer?->is_vip) {
            return KitchenOrder::PRIORITY_VIP;
        }

        // Rush orders (can be set on order)
        if ($order->is_rush ?? false) {
            return KitchenOrder::PRIORITY_RUSH;
        }

        return KitchenOrder::PRIORITY_NORMAL;
    }

    /**
     * Estimate preparation time for item
     */
    protected function estimateTime(OrderItem $item): ?int
    {
        $product = $item->product;
        if (!$product) {
            return null;
        }

        // Base time from product
        $baseTime = $product->prep_time ?? 10;

        // Multiply by quantity (with diminishing returns)
        return (int) ($baseTime + (($item->quantity - 1) * ($baseTime * 0.5)));
    }

    /**
     * Get available stations
     */
    public function getStations(): array
    {
        return KitchenOrder::distinct()
            ->whereNotNull('station')
            ->pluck('station')
            ->toArray();
    }
}
