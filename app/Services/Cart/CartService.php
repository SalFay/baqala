<?php

namespace App\Services\Cart;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SellingPriceGroup;
use App\Services\Promotion\DiscountContext;
use App\Services\Promotion\DiscountResult;
use App\Services\Promotion\PromotionEngine;
use Illuminate\Support\Facades\Auth;

class CartService
{
    protected ?Cart $cart = null;
    protected ?PromotionEngine $promotionEngine = null;
    protected ?string $appliedCouponCode = null;

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

        // Load relationships and recalculate prices based on new customer
        $cart->load(['items.product', 'items.productVariant', 'customer.customerGroup']);

        // Recalculate prices if cart has items (customer may have different price group)
        if ($cart->items->count() > 0) {
            $cart->recalculatePrices();
        }

        return $cart->fresh(['items.product', 'customer']);
    }

    public function setPriceGroup(?int $priceGroupId): Cart
    {
        $cart = $this->getCart();
        $cart->update(['selling_price_group_id' => $priceGroupId]);

        // Recalculate prices if cart has items
        $cart->load(['items.product', 'items.productVariant', 'sellingPriceGroup']);

        if ($cart->items->count() > 0) {
            $cart->recalculatePrices();
        }

        return $cart->fresh(['items.product', 'customer', 'sellingPriceGroup']);
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

    /**
     * Apply a coupon code to the cart
     */
    public function applyCoupon(string $code): array
    {
        $cart = $this->getCart();

        $coupon = Coupon::where('code', strtoupper(trim($code)))->first();

        if (!$coupon) {
            return [
                'success' => false,
                'message' => 'Invalid coupon code',
            ];
        }

        // Validate coupon
        $errors = $coupon->validateForCustomer($cart->customer, $cart->subtotal);

        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => implode('. ', $errors),
            ];
        }

        // Calculate coupon discount
        $couponDiscount = $coupon->discount_type === 'percentage'
            ? ($cart->subtotal * $coupon->discount_amount) / 100
            : $coupon->discount_amount;

        // Apply max discount cap if set
        if ($coupon->max_discount_amount && $couponDiscount > $coupon->max_discount_amount) {
            $couponDiscount = $coupon->max_discount_amount;
        }

        // Store coupon code and discount in cart
        $cart->update([
            'coupon_code' => $coupon->code,
            'coupon_id' => $coupon->id,
            'coupon_discount' => $couponDiscount,
        ]);

        // Recalculate cart totals
        $cart->recalculate();

        $this->appliedCouponCode = $coupon->code;

        return [
            'success' => true,
            'coupon' => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'discount_type' => $coupon->discount_type,
                'discount_amount' => $coupon->discount_amount,
                'coupon_discount' => $couponDiscount,
            ],
            'message' => 'Coupon applied successfully',
        ];
    }

    /**
     * Remove coupon from cart
     */
    public function removeCoupon(): Cart
    {
        $cart = $this->getCart();
        $cart->update([
            'coupon_code' => null,
            'coupon_id' => null,
            'coupon_discount' => 0,
        ]);

        $this->appliedCouponCode = null;

        return $cart->recalculate();
    }

    /**
     * Get the promotion engine instance
     */
    protected function getPromotionEngine(): PromotionEngine
    {
        return $this->promotionEngine ??= new PromotionEngine();
    }

    /**
     * Calculate all applicable discounts for the current cart
     */
    public function calculatePromotions(?string $paymentMethod = null): DiscountResult
    {
        $cart = $this->getCart();
        $cart->load(['items.product', 'customer.customerGroup', 'customer.loyalty.tier']);

        $couponCode = $this->appliedCouponCode ?? $cart->coupon_code;

        return $this->getPromotionEngine()->calculateCartDiscounts(
            $cart,
            $couponCode,
            $paymentMethod
        );
    }

    /**
     * Get all available promotions/discounts that could apply
     */
    public function getAvailablePromotions(): array
    {
        $cart = $this->getCart();
        $cart->load(['items.product', 'customer.customerGroup', 'customer.loyalty.tier']);

        $context = DiscountContext::fromCart($cart);

        return $this->getPromotionEngine()->previewAvailableDiscounts($context);
    }

    /**
     * Get cart with promotion details
     */
    public function getCartWithPromotions(?string $paymentMethod = null): array
    {
        $cart = $this->getCart();
        $cart->load([
            'items.product',
            'items.productVariant',
            'customer.customerGroup.sellingPriceGroup',
            'customer.loyalty.tier',
        ]);

        $promotions = $this->calculatePromotions($paymentMethod);

        return [
            'cart' => $cart,
            'promotions' => $promotions->toArray(),
            'effective_total' => $promotions->finalTotal,
            'savings' => $promotions->totalDiscount,
            'savings_percentage' => $promotions->getSavingsPercentage(),
            'free_shipping' => $promotions->hasFreeShipping,
            'free_items' => $promotions->freeItems,
        ];
    }

    /**
     * Record promotion usage after successful checkout
     */
    public function recordPromotionUsage(int $orderId, DiscountResult $promotions): void
    {
        $cart = $this->getCart();
        $customerId = $cart->customer_id;

        $this->getPromotionEngine()->recordUsage($promotions, $orderId, $customerId);
    }
}
