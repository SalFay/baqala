<?php

namespace App\Services\Cart;

use App\Models\Cart;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;

class CartService
{
    protected ?Cart $cart = null;

    public function getCart(): Cart
    {
        return $this->cart ??= Cart::with(['items.product', 'customer'])
            ->forUser(Auth::id())
            ->active()
            ->first()
            ?? Cart::create([
                'user_id' => Auth::id(),
                'store_id' => Auth::user()?->store_id ?? 1,
                'status' => 'active',
            ]);
    }

    public function addItem(Product $product, int $qty = 1, ?ProductVariant $variant = null): Cart
    {
        $cart = $this->getCart();

        // Server-side stock validation
        if ($product->track_inventory) {
            $currentCartQty = $cart->items()
                ->where('product_id', $product->id)
                ->where('product_variant_id', $variant?->id)
                ->value('quantity') ?? 0;

            $totalRequestedQty = $currentCartQty + $qty;
            $availableStock = $variant
                ? $variant->getStockQuantity()
                : $product->getStockQuantity();

            if ($totalRequestedQty > $availableStock) {
                throw new \Exception(
                    "Insufficient stock. Available: {$availableStock}, Requested: {$totalRequestedQty}"
                );
            }
        }

        $cart->addItem($product, $qty, $variant);
        return $cart->recalculate();
    }

    public function updateItemQuantity(int $itemId, int $qty): Cart
    {
        $cart = $this->getCart();
        $cart->updateItemQty($itemId, $qty);
        return $cart;
    }

    public function removeItem(int $itemId): Cart
    {
        $cart = $this->getCart();
        $cart->removeItem($itemId);
        return $cart;
    }

    public function setCustomer(?Customer $customer): Cart
    {
        $cart = $this->getCart();
        $cart->update(['customer_id' => $customer?->id]);
        return $cart->fresh(['items.product', 'customer']);
    }

    public function clearCart(): void
    {
        $this->getCart()->clear();
        $this->cart = null;
    }

    public function holdCart(string $name): void
    {
        $this->getCart()->hold($name);
        $this->cart = null;
    }

    public function getHeldCarts()
    {
        return Cart::with(['items', 'customer'])
            ->forUser(Auth::id())
            ->held()
            ->latest('held_at')
            ->get();
    }

    public function restoreHeldCart(int $cartId, bool $holdCurrentCart = false): Cart
    {
        $currentActiveCart = Cart::forUser(Auth::id())->active()->first();

        // If there's an active cart with items, optionally hold it first
        if ($currentActiveCart && $currentActiveCart->items()->count() > 0) {
            if ($holdCurrentCart) {
                // Auto-hold the current cart with a generated name
                $currentActiveCart->hold('Auto-held ' . now()->format('H:i'));
            } else {
                // Clear the current cart items and reset totals
                $currentActiveCart->clear();
                $currentActiveCart->delete();
            }
        } elseif ($currentActiveCart) {
            // Empty cart, just delete it
            $currentActiveCart->delete();
        }

        // Restore held cart
        $cart = Cart::findOrFail($cartId);
        $cart->restore();
        return $this->cart = $cart;
    }

    public function findProductByBarcode(string $barcode): ?array
    {
        if ($variant = ProductVariant::where('barcode', $barcode)->first()) {
            return ['product' => $variant->product, 'variant' => $variant];
        }
        if ($product = Product::where('barcode', $barcode)->orWhere('sku', $barcode)->first()) {
            return ['product' => $product, 'variant' => null];
        }
        return null;
    }

    public function applyDiscount(float $amount, string $type, ?string $reason = null): Cart
    {
        $cart = $this->getCart();

        if ($type === 'percentage' && ($amount < 0 || $amount > 100)) {
            throw new \InvalidArgumentException('Percentage discount must be between 0 and 100');
        }

        if ($type === 'fixed' && $amount > $cart->subtotal) {
            throw new \InvalidArgumentException('Discount cannot exceed subtotal');
        }

        $cart->update([
            'discount' => $amount,
            'discount_type' => $type,
            'discount_reason' => $reason,
        ]);

        return $cart->recalculate();
    }

    public function removeDiscount(): Cart
    {
        $cart = $this->getCart();
        $cart->update([
            'discount' => 0,
            'discount_type' => null,
            'discount_reason' => null,
        ]);

        return $cart->recalculate();
    }
}
