<?php

namespace App\Services\Return;

use App\Enums\InventoryMovementType;
use App\Enums\ReturnStatus;
use App\Enums\ReturnType;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\OrderReturnItem;
use App\Services\Inventory\InventoryService;
use App\Services\Loyalty\LoyaltyService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReturnService
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected LoyaltyService $loyaltyService
    ) {}

    public function createReturn(
        Order $order,
        array $items,
        ReturnType $type,
        ?int $returnReasonId = null,
        ?string $reason = null,
        ?string $notes = null
    ): OrderReturn {
        if (!$order->canBeReturned()) {
            throw new \InvalidArgumentException('Order cannot be returned');
        }

        return DB::transaction(function () use ($order, $items, $type, $returnReasonId, $reason, $notes) {
            $subtotal = 0;
            $taxAmount = 0;

            // Validate items and calculate totals
            foreach ($items as $item) {
                $orderItem = $order->items()->findOrFail($item['order_item_id']);
                $returnableQty = $orderItem->returnable_quantity;

                if ($item['quantity'] > $returnableQty) {
                    throw new \InvalidArgumentException(
                        "Cannot return more than {$returnableQty} units of {$orderItem->display_name}"
                    );
                }

                $itemSubtotal = $item['quantity'] * $orderItem->sale_price;
                $itemTax = ($itemSubtotal * $orderItem->tax_rate) / 100;

                $subtotal += $itemSubtotal;
                $taxAmount += $itemTax;
            }

            $totalAmount = $subtotal + $taxAmount;

            // Create return record
            $return = OrderReturn::create([
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'store_id' => $order->store_id,
                'processed_by' => Auth::id(),
                'type' => $type,
                'status' => ReturnStatus::PENDING,
                'return_reason_id' => $returnReasonId,
                'reason' => $reason,
                'notes' => $notes,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'refund_amount' => $totalAmount,
            ]);

            // Create return items
            foreach ($items as $item) {
                $orderItem = $order->items()->find($item['order_item_id']);

                OrderReturnItem::create([
                    'order_return_id' => $return->id,
                    'order_item_id' => $orderItem->id,
                    'product_id' => $orderItem->product_id,
                    'product_variant_id' => $orderItem->product_variant_id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $orderItem->sale_price,
                    'tax_amount' => ($item['quantity'] * $orderItem->sale_price * $orderItem->tax_rate) / 100,
                    'total' => $item['quantity'] * $orderItem->sale_price * (1 + $orderItem->tax_rate / 100),
                    'condition' => $item['condition'] ?? 'sellable',
                    'restock' => $item['restock'] ?? true,
                    'reason' => $item['reason'] ?? null,
                ]);
            }

            return $return->fresh(['items', 'order']);
        });
    }

    public function approveReturn(OrderReturn $return): OrderReturn
    {
        if (!$return->canApprove()) {
            throw new \InvalidArgumentException('Return cannot be approved');
        }

        $return->update([
            'status' => ReturnStatus::APPROVED,
            'approved_at' => now(),
        ]);

        return $return->fresh();
    }

    public function rejectReturn(OrderReturn $return, ?string $reason = null): OrderReturn
    {
        if (!$return->canReject()) {
            throw new \InvalidArgumentException('Return cannot be rejected');
        }

        $return->update([
            'status' => ReturnStatus::REJECTED,
            'notes' => $return->notes . "\nRejected: " . ($reason ?? 'No reason provided'),
        ]);

        return $return->fresh();
    }

    public function processReturn(
        OrderReturn $return,
        string $refundMethod,
        ?float $restockingFee = 0
    ): OrderReturn {
        if (!$return->canProcess()) {
            throw new \InvalidArgumentException('Return cannot be processed');
        }

        return DB::transaction(function () use ($return, $refundMethod, $restockingFee) {
            // Restore inventory for restockable items
            foreach ($return->items as $item) {
                if ($item->restock && $item->isSellable()) {
                    $this->inventoryService->recordMovement(
                        storeId: $return->store_id,
                        productId: $item->product_id,
                        variantId: $item->product_variant_id,
                        type: InventoryMovementType::RETURN_FROM_CUSTOMER,
                        quantity: $item->quantity,
                        referenceType: OrderReturn::class,
                        referenceId: $return->id
                    );
                }
            }

            // Calculate refund amount
            $refundAmount = $return->total_amount - $restockingFee;

            // Handle loyalty points
            $order = $return->order;

            if ($order->loyalty_points_earned > 0 && $order->customer) {
                // Calculate proportional points to deduct
                $refundRatio = $return->total_amount / $order->total;
                $pointsToDeduct = (int) round($order->loyalty_points_earned * $refundRatio);

                if ($pointsToDeduct > 0) {
                    $this->loyaltyService->deductPoints(
                        $order->customer,
                        $pointsToDeduct,
                        $return,
                        'Return processed'
                    );
                }
            }

            $return->update([
                'status' => ReturnStatus::COMPLETED,
                'refund_amount' => $refundAmount,
                'restocking_fee' => $restockingFee,
                'refund_method' => $refundMethod,
                'completed_at' => now(),
            ]);

            return $return->fresh();
        });
    }

    public function getReturnableItems(Order $order): \Illuminate\Support\Collection
    {
        return $order->items->map(function ($item) {
            return [
                'order_item_id' => $item->id,
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'name' => $item->display_name,
                'quantity_ordered' => $item->stock,
                'quantity_returned' => $item->returned_quantity,
                'quantity_returnable' => $item->returnable_quantity,
                'unit_price' => $item->sale_price,
            ];
        })->filter(fn($item) => $item['quantity_returnable'] > 0);
    }
}
