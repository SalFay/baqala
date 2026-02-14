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
            ?? Cart::create(['user_id' => Auth::id(), 'status' => 'active']);
    }

    public function addItem(Product $product, int $qty = 1, ?ProductVariant $variant = null): Cart
    {
        $cart = $this->getCart();
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

    public function restoreHeldCart(int $cartId): Cart
    {
        // Clear current active cart
        Cart::forUser(Auth::id())->active()->delete();

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
}
