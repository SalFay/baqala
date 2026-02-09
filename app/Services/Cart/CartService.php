<?php

namespace App\Services\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartService
{
    protected ?Cart $cart = null;

    public function getCart(?int $storeId = null): Cart
    {
        if ($this->cart) {
            return $this->cart;
        }

        $userId = Auth::id();

        $this->cart = Cart::query()
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->when($storeId, fn($q) => $q->where('store_id', $storeId))
            ->first();

        if (!$this->cart) {
            $this->cart = Cart::create([
                'user_id' => $userId,
                'store_id' => $storeId,
                'status' => 'active',
            ]);
        }

        return $this->cart;
    }

    public function addItem(
        Product $product,
        int $quantity = 1,
        ?ProductVariant $variant = null,
        ?int $storeId = null
    ): CartItem {
        $cart = $this->getCart($storeId);

        return DB::transaction(function () use ($cart, $product, $quantity, $variant) {
            $item = $cart->addItem($product, $quantity, $variant);
            $cart->calculateTotals();
            return $item;
        });
    }

    public function updateItemQuantity(int $itemId, int $quantity): CartItem
    {
        $cart = $this->getCart();
        $item = $cart->items()->findOrFail($itemId);

        if ($quantity <= 0) {
            $item->delete();
            $cart->calculateTotals();
            return $item;
        }

        $item->quantity = $quantity;
        $item->calculateTotals();
        $item->save();

        $cart->calculateTotals();

        return $item;
    }

    public function removeItem(int $itemId): void
    {
        $cart = $this->getCart();
        $cart->items()->where('id', $itemId)->delete();
        $cart->calculateTotals();
    }

    public function setCustomer(?Customer $customer): Cart
    {
        $cart = $this->getCart();
        $cart->update(['customer_id' => $customer?->id]);
        return $cart->fresh();
    }

    public function applyDiscount(float $amount, string $type = 'fixed', ?string $reason = null): Cart
    {
        $cart = $this->getCart();

        $cart->update([
            'discount' => $amount,
            'discount_type' => $type,
            'discount_reason' => $reason,
        ]);

        $cart->calculateTotals();

        return $cart->fresh();
    }

    public function removeDiscount(): Cart
    {
        $cart = $this->getCart();

        $cart->update([
            'discount' => 0,
            'discount_type' => null,
            'discount_reason' => null,
        ]);

        $cart->calculateTotals();

        return $cart->fresh();
    }

    public function setLoyaltyPoints(int $points): Cart
    {
        $cart = $this->getCart();

        if (!$cart->customer) {
            throw new \InvalidArgumentException('Customer is required to redeem loyalty points');
        }

        $loyalty = $cart->customer->loyalty;

        if (!$loyalty || $points > $loyalty->points_balance) {
            throw new \InvalidArgumentException('Insufficient loyalty points');
        }

        // Calculate discount (e.g., 1 point = 0.01 SAR)
        $pointValue = (float) \App\Models\Setting::get('loyalty_point_value', 0.01);
        $discount = $points * $pointValue;

        $cart->update([
            'loyalty_points_to_redeem' => $points,
            'loyalty_discount' => $discount,
        ]);

        $cart->calculateTotals();

        return $cart->fresh();
    }

    public function clearCart(): void
    {
        $cart = $this->getCart();
        $cart->clear();
        $this->cart = null;
    }

    public function holdCart(string $name): Cart
    {
        $cart = $this->getCart();
        $cart->hold($name);
        $this->cart = null;
        return $cart;
    }

    public function getHeldCarts(?int $storeId = null): \Illuminate\Database\Eloquent\Collection
    {
        return Cart::query()
            ->with(['items', 'customer'])
            ->where('user_id', Auth::id())
            ->where('status', 'held')
            ->when($storeId, fn($q) => $q->where('store_id', $storeId))
            ->orderByDesc('held_at')
            ->get();
    }

    public function restoreHeldCart(int $cartId): Cart
    {
        // First clear current active cart
        $activeCart = Cart::query()
            ->where('user_id', Auth::id())
            ->where('status', 'active')
            ->first();

        if ($activeCart) {
            $activeCart->delete();
        }

        // Restore held cart
        $heldCart = Cart::findOrFail($cartId);
        $heldCart->restore();

        $this->cart = $heldCart;

        return $heldCart;
    }

    public function findProductByBarcode(string $barcode, ?int $storeId = null): ?array
    {
        // Check product variants first
        $variant = ProductVariant::where('barcode', $barcode)->first();

        if ($variant) {
            return [
                'product' => $variant->product,
                'variant' => $variant,
            ];
        }

        // Check products
        $product = Product::where('barcode', $barcode)->first();

        if ($product) {
            return [
                'product' => $product,
                'variant' => null,
            ];
        }

        return null;
    }

    public function getCartSummary(): array
    {
        $cart = $this->getCart();

        return [
            'items_count' => $cart->items->sum('quantity'),
            'subtotal' => $cart->subtotal,
            'tax_amount' => $cart->tax_amount,
            'discount' => $cart->discount,
            'discount_type' => $cart->discount_type,
            'loyalty_discount' => $cart->loyalty_discount,
            'total' => $cart->total,
            'customer' => $cart->customer,
        ];
    }
}
