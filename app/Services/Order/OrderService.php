<?php

namespace App\Services\Order;

use App\Enums\InventoryMovementType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Services\Inventory\InventoryService;
use App\Services\Loyalty\LoyaltyService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected LoyaltyService $loyaltyService
    ) {}

    public function createOrderFromCart(
        Cart $cart,
        string $paymentType,
        array $paymentDetails = []
    ): Order {
        if ($cart->items->isEmpty()) {
            throw new \InvalidArgumentException('Cart is empty');
        }

        return DB::transaction(function () use ($cart, $paymentType, $paymentDetails) {
            // Create order
            $order = Order::create([
                'store_id' => $cart->store_id,
                'customer_id' => $cart->customer_id,
                'user_id' => Auth::id(),
                'payment_type' => $paymentType,
                'date' => now(),
                'status' => OrderStatus::COMPLETED,
                'payment_status' => PaymentStatus::PAID,
                'sub_total' => $cart->subtotal,
                'tax_amount' => $cart->tax_amount,
                'discount' => $this->calculateTotalDiscount($cart),
                'discount_type' => $cart->discount_type,
                'total' => $cart->total,
                'vat' => $cart->tax_amount,
                'customer_name' => $cart->customer?->full_name,
                'cashier_name' => Auth::user()?->name,
                'loyalty_points_redeemed' => $cart->loyalty_points_to_redeem,
                'loyalty_discount' => $cart->loyalty_discount,
            ]);

            // Create order items
            foreach ($cart->items as $cartItem) {
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'product_variant_id' => $cartItem->product_variant_id,
                    'sku' => $cartItem->sku,
                    'product_name' => $cartItem->product_name,
                    'variant_name' => $cartItem->variant_name,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $cartItem->unit_price,
                    'cost_price' => $cartItem->purchase_price,
                    'discount' => $cartItem->discount ?? 0,
                    'discount_percent' => $cartItem->discount_type === 'percentage' ? $cartItem->discount : 0,
                    'tax_rate' => $cartItem->tax_rate ?? 0,
                    'tax_amount' => $cartItem->tax_amount ?? 0,
                    'line_total' => $cartItem->line_total,
                ]);

                // Deduct inventory (only if store is assigned and product tracks inventory)
                if ($cart->store_id && $cartItem->product?->track_inventory) {
                    $this->inventoryService->recordMovement(
                        storeId: $cart->store_id,
                        productId: $cartItem->product_id,
                        variantId: $cartItem->product_variant_id,
                        type: InventoryMovementType::SALE,
                        quantity: -$cartItem->quantity,
                        unitCost: $cartItem->purchase_price ?? 0,
                        referenceType: Order::class,
                        referenceId: $order->id
                    );
                }
            }

            // Create payment record
            if (!empty($paymentDetails)) {
                Payment::create([
                    'order_id' => $order->id,
                    'amount' => $order->total,
                    'payment_type' => $paymentType,
                    'reference' => $paymentDetails['reference'] ?? null,
                    'notes' => $paymentDetails['notes'] ?? null,
                ]);
            }

            // Process loyalty points
            if ($cart->loyalty_points_to_redeem > 0 && $cart->customer) {
                $this->loyaltyService->redeemPoints(
                    $cart->customer,
                    $cart->loyalty_points_to_redeem,
                    $order
                );
            }

            // Award loyalty points for purchase
            if ($cart->customer) {
                $pointsEarned = $this->loyaltyService->calculatePointsForPurchase($order->total);
                if ($pointsEarned > 0) {
                    $this->loyaltyService->awardPoints($cart->customer, $pointsEarned, $order);
                    $order->update(['loyalty_points_earned' => $pointsEarned]);
                }
            }

            // Clear cart
            $cart->update(['status' => 'converted']);

            return $order->fresh(['items', 'customer', 'user']);
        });
    }

    protected function calculateTotalDiscount(Cart $cart): float
    {
        $discount = 0;

        if ($cart->discount > 0) {
            if ($cart->discount_type === 'percentage') {
                $discount = ($cart->subtotal * $cart->discount) / 100;
            } else {
                $discount = $cart->discount;
            }
        }

        return $discount + $cart->loyalty_discount;
    }

    public function cancelOrder(Order $order, ?string $reason = null): Order
    {
        if ($order->status === OrderStatus::CANCELLED) {
            throw new \InvalidArgumentException('Order is already cancelled');
        }

        return DB::transaction(function () use ($order, $reason) {
            // Restore inventory
            foreach ($order->items as $item) {
                if ($item->product?->track_inventory) {
                    $this->inventoryService->recordMovement(
                        storeId: $order->store_id,
                        productId: $item->product_id,
                        variantId: $item->product_variant_id,
                        type: InventoryMovementType::ADJUSTMENT_ADD,
                        quantity: $item->quantity,
                        reason: 'Order cancelled: ' . ($reason ?? 'No reason provided'),
                        referenceType: Order::class,
                        referenceId: $order->id
                    );
                }
            }

            // Refund loyalty points if any were redeemed
            if ($order->loyalty_points_redeemed > 0 && $order->customer) {
                $this->loyaltyService->refundPoints(
                    $order->customer,
                    $order->loyalty_points_redeemed,
                    $order
                );
            }

            // Deduct earned loyalty points
            if ($order->loyalty_points_earned > 0 && $order->customer) {
                $this->loyaltyService->deductPoints(
                    $order->customer,
                    $order->loyalty_points_earned,
                    $order,
                    'Order cancelled'
                );
            }

            $order->update([
                'status' => OrderStatus::CANCELLED,
                'payment_status' => PaymentStatus::CANCELLED,
            ]);

            return $order->fresh();
        });
    }

    public function getOrderReceipt(Order $order): array
    {
        $order->load(['items.product', 'customer', 'user', 'store']);

        return [
            'order' => $order,
            'store' => $order->store,
            'items' => $order->items->map(fn($item) => [
                'name' => $item->display_name,
                'quantity' => $item->quantity,
                'price' => $item->sale_price,
                'total' => $item->line_total,
            ]),
            'subtotal' => $order->sub_total,
            'tax' => $order->tax_amount,
            'discount' => $order->discount,
            'total' => $order->total,
            'payment_type' => $order->payment_type,
            'cashier' => $order->cashier_name,
            'customer' => $order->customer?->full_name,
            'date' => $order->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
