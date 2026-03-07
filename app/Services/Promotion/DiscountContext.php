<?php

namespace App\Services\Promotion;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\LoyaltyTier;
use App\Models\Store;
use Illuminate\Support\Carbon;

/**
 * DiscountContext - Immutable DTO holding all context needed for discount evaluation
 */
class DiscountContext
{
    public readonly ?Customer $customer;
    public readonly ?int $customerGroupId;
    public readonly ?LoyaltyTier $loyaltyTier;
    public readonly ?Store $store;
    public readonly float $subtotal;
    public readonly int $totalQuantity;
    public readonly array $items;
    public readonly array $categoryIds;
    public readonly array $brandIds;
    public readonly array $productIds;
    public readonly Carbon $datetime;
    public readonly int $dayOfWeek;
    public readonly string $time;
    public readonly ?string $paymentMethod;
    public readonly bool $isFirstOrder;

    public function __construct(
        ?Customer $customer = null,
        ?Store $store = null,
        array $items = [],
        ?string $paymentMethod = null,
        ?Carbon $datetime = null
    ) {
        $this->customer = $customer;
        $this->customerGroupId = $customer?->customer_group_id;
        $this->loyaltyTier = $customer?->loyalty?->tier;
        $this->store = $store;
        $this->paymentMethod = $paymentMethod;
        $this->datetime = $datetime ?? now();
        $this->dayOfWeek = $this->datetime->dayOfWeek ?: 7; // 1=Monday, 7=Sunday
        $this->time = $this->datetime->format('H:i');
        $this->items = $items;

        // Pre-calculate aggregates
        $this->subtotal = collect($items)->sum(fn($item) => $this->getItemTotal($item));
        $this->totalQuantity = collect($items)->sum(fn($item) => $this->getItemQuantity($item));

        // Pre-extract unique IDs for fast lookup
        $this->categoryIds = collect($items)
            ->map(fn($item) => $this->getItemCategoryId($item))
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $this->brandIds = collect($items)
            ->map(fn($item) => $this->getItemBrandId($item))
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $this->productIds = collect($items)
            ->map(fn($item) => $this->getItemProductId($item))
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $this->isFirstOrder = $customer ? $customer->orders()->completed()->count() === 0 : false;
    }

    public static function fromCart(Cart $cart, ?string $paymentMethod = null): self
    {
        return new self(
            customer: $cart->customer,
            store: $cart->store,
            items: $cart->items->toArray(),
            paymentMethod: $paymentMethod,
        );
    }

    public static function forProduct(
        $product,
        int $quantity = 1,
        ?Customer $customer = null,
        ?Store $store = null
    ): self {
        $item = [
            'product_id' => $product->id,
            'category_id' => $product->category_id,
            'brand_id' => $product->brand_id,
            'quantity' => $quantity,
            'unit_price' => $product->sale_price ?? $product->price,
            'line_total' => ($product->sale_price ?? $product->price) * $quantity,
        ];

        return new self(
            customer: $customer,
            store: $store,
            items: [$item],
        );
    }

    protected function getItemTotal($item): float
    {
        if ($item instanceof CartItem) {
            return $item->line_total;
        }
        return $item['line_total'] ?? (($item['unit_price'] ?? 0) * ($item['quantity'] ?? 1));
    }

    protected function getItemQuantity($item): int
    {
        if ($item instanceof CartItem) {
            return $item->quantity;
        }
        return $item['quantity'] ?? 1;
    }

    protected function getItemCategoryId($item): ?int
    {
        if ($item instanceof CartItem) {
            return $item->product?->category_id;
        }
        return $item['category_id'] ?? null;
    }

    protected function getItemBrandId($item): ?int
    {
        if ($item instanceof CartItem) {
            return $item->product?->brand_id;
        }
        return $item['brand_id'] ?? null;
    }

    protected function getItemProductId($item): ?int
    {
        if ($item instanceof CartItem) {
            return $item->product_id;
        }
        return $item['product_id'] ?? null;
    }

    public function hasProduct(int $productId): bool
    {
        return in_array($productId, $this->productIds);
    }

    public function hasCategory(int $categoryId): bool
    {
        return in_array($categoryId, $this->categoryIds);
    }

    public function hasBrand(int $brandId): bool
    {
        return in_array($brandId, $this->brandIds);
    }

    public function getQuantityForProduct(int $productId): int
    {
        return collect($this->items)
            ->filter(fn($item) => $this->getItemProductId($item) === $productId)
            ->sum(fn($item) => $this->getItemQuantity($item));
    }

    public function isWithinTimeRange(string $start, string $end): bool
    {
        $current = $this->datetime->format('H:i');
        return $current >= $start && $current <= $end;
    }

    public function isOnDayOfWeek(array $days): bool
    {
        return in_array($this->dayOfWeek, $days);
    }
}
