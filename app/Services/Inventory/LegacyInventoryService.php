<?php

namespace App\Services\Inventory;

use App\Enums\InventoryStatus;
use App\Models\Inventory;
use App\Models\InventoryLog;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;

/**
 * Service for handling legacy inventory operations (Admin panel)
 * Works with the old Inventory model structure
 */
class LegacyInventoryService
{
    /**
     * Mark inventory items as sold for an order
     */
    public function markAsSold(int $productId, int $orderId, int $quantity, string $date = null): void
    {
        $date = $date ?? now()->toDateString();

        for ($i = 1; $i <= $quantity; $i++) {
            $inventory = Inventory::where('product_id', $productId)
                ->where('status', InventoryStatus::AVAILABLE->value)
                ->first();

            if ($inventory) {
                $inventory->update([
                    'status' => InventoryStatus::SOLD->value,
                    'order_id' => $orderId
                ]);
            } else {
                // Create virtual inventory for items sold without stock
                $product = \App\Models\Product::find($productId);
                Inventory::create([
                    'stock_id' => 0,
                    'order_id' => $orderId,
                    'product_id' => $productId,
                    'cost' => $product?->purchase_price ?? 0,
                    'status' => InventoryStatus::SOLD->value,
                    'date' => $date
                ]);
            }
        }
    }

    /**
     * Mark inventory items as available (e.g., when order is deleted/returned)
     */
    public function markAsAvailable(int $productId, int $orderId, int $quantity): void
    {
        for ($i = 1; $i <= $quantity; $i++) {
            $inventory = Inventory::where('product_id', $productId)
                ->where('order_id', $orderId)
                ->where('status', InventoryStatus::SOLD->value)
                ->first();

            if ($inventory) {
                $inventory->update([
                    'status' => InventoryStatus::AVAILABLE->value,
                    'order_id' => 0
                ]);
            }
        }
    }

    /**
     * Create inventory entries from stock purchase
     */
    public function createStockInventory(
        int $stockId,
        int $productId,
        int $quantity,
        float $cost,
        string $date = null
    ): void {
        $date = $date ?? now()->toDateString();

        for ($i = 1; $i <= $quantity; $i++) {
            // Check for existing sold items without stock_id
            $existing = Inventory::where('product_id', $productId)
                ->where('status', InventoryStatus::SOLD->value)
                ->where('stock_id', 0)
                ->first();

            if ($existing) {
                $existing->update(['stock_id' => $stockId]);
            } else {
                Inventory::create([
                    'stock_id' => $stockId,
                    'product_id' => $productId,
                    'cost' => $cost,
                    'status' => InventoryStatus::AVAILABLE->value,
                    'date' => $date
                ]);
            }
        }
    }

    /**
     * Create order item record
     */
    public function createOrderItem(
        int $orderId,
        int $productId,
        int $quantity,
        float $purchasePrice,
        float $salePrice,
        float $taxablePrice,
        string $date = null
    ): OrderItem {
        return OrderItem::create([
            'order_type' => Order::class,
            'order_id' => $orderId,
            'product_id' => $productId,
            'stock' => $quantity,
            'purchase_price' => $purchasePrice,
            'sale_price' => $salePrice,
            'taxable_price' => round($taxablePrice, 2),
            'status' => 'Delivered',
            'date' => $date ?? now()->toDateString()
        ]);
    }

    /**
     * Create inventory log entry
     */
    public function createInventoryLog(
        string $orderType,
        int $orderId,
        int $productId,
        int $quantity,
        float $cost,
        string $status,
        float $taxablePrice = 0,
        string $date = null
    ): InventoryLog {
        return InventoryLog::create([
            'order_type' => $orderType,
            'order_id' => $orderId,
            'product_id' => $productId,
            'stock' => $quantity,
            'cost' => $cost,
            'taxable_price' => round($taxablePrice, 2),
            'status' => $status,
            'date' => $date ?? now()->toDateString()
        ]);
    }

    /**
     * Process order items for a new sale
     */
    public function processSaleItems(int $orderId, array $products, string $date): void
    {
        foreach ($products as $product) {
            $this->markAsSold($product['id'], $orderId, $product['stock'], $date);

            $this->createOrderItem(
                $orderId,
                $product['id'],
                $product['stock'],
                $product['purchase_price'],
                $product['sale_price'],
                $product['taxable_price'] ?? $product['sale_price'],
                $date
            );

            $this->createInventoryLog(
                Order::class,
                $orderId,
                $product['id'],
                $product['stock'],
                $product['sale_price'],
                InventoryStatus::SOLD->value,
                $product['taxable_price'] ?? 0,
                $date
            );
        }
    }

    /**
     * Process stock purchase items
     */
    public function processStockItems(int $stockId, array $products, string $date): void
    {
        foreach ($products as $product) {
            $this->createStockInventory(
                $stockId,
                $product['id'],
                $product['stock'],
                $product['pprice'],
                $date
            );

            $this->createInventoryLog(
                Stock::class,
                $stockId,
                $product['id'],
                $product['stock'],
                $product['pprice'],
                InventoryStatus::AVAILABLE->value,
                0,
                $date
            );
        }
    }

    /**
     * Reverse order items when deleting an order
     */
    public function reverseOrderItems(Order $order): void
    {
        foreach ($order->items as $item) {
            $this->markAsAvailable($item->product_id, $order->id, $item->stock);

            InventoryLog::where('order_id', $order->id)
                ->where('order_type', Order::class)
                ->delete();

            $item->delete();
        }
    }

    /**
     * Get available stock count for a product
     */
    public function getAvailableStock(int $productId): int
    {
        return Inventory::where('product_id', $productId)
            ->where('status', InventoryStatus::AVAILABLE->value)
            ->count();
    }

    /**
     * Get sold stock count for a product
     */
    public function getSoldStock(int $productId): int
    {
        return Inventory::where('product_id', $productId)
            ->where('status', InventoryStatus::SOLD->value)
            ->count();
    }
}
