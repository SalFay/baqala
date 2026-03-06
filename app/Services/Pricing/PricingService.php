<?php

namespace App\Services\Pricing;

use App\Models\BulkDiscount;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\Product;
use App\Models\ProductPriceGroupPrice;
use App\Models\ProductVariant;
use App\Models\SellingPriceGroup;

class PricingService
{
    protected ?SellingPriceGroup $priceGroup = null;
    protected ?CustomerGroup $customerGroup = null;
    protected ?Customer $customer = null;

    /**
     * Set the customer for pricing calculations.
     */
    public function setCustomer(?Customer $customer): self
    {
        $this->customer = $customer;

        if ($customer) {
            $this->customerGroup = $customer->customerGroup;

            // Use customer group's price group if available
            if ($this->customerGroup?->sellingPriceGroup) {
                $this->priceGroup = $this->customerGroup->sellingPriceGroup;
            }
        }

        return $this;
    }

    /**
     * Set the selling price group directly.
     */
    public function setPriceGroup(?SellingPriceGroup $priceGroup): self
    {
        $this->priceGroup = $priceGroup;
        return $this;
    }

    /**
     * Get the effective price for a product.
     */
    public function getPrice(Product $product, ?ProductVariant $variant = null): array
    {
        $basePrice = $this->getBasePrice($product, $variant);
        $priceGroupPrice = $this->getPriceGroupPrice($product, $variant);
        $finalPrice = $priceGroupPrice ?? $basePrice;

        // Apply customer group discount if applicable
        if ($this->customerGroup && $this->customerGroup->discount_percent > 0) {
            $discount = $finalPrice * ($this->customerGroup->discount_percent / 100);
            $finalPrice = $finalPrice - $discount;
        }

        return [
            'base_price' => $basePrice,
            'price_group_price' => $priceGroupPrice,
            'final_price' => round($finalPrice, 2),
            'price_group_id' => $this->priceGroup?->id,
            'price_group_name' => $this->priceGroup?->name,
            'customer_group_discount' => $this->customerGroup?->discount_percent ?? 0,
        ];
    }

    /**
     * Get base price for a product/variant.
     */
    protected function getBasePrice(Product $product, ?ProductVariant $variant = null): float
    {
        if ($variant) {
            return (float) ($variant->sale_price ?? $variant->price ?? $product->sale_price ?? $product->price ?? 0);
        }

        return (float) ($product->sale_price ?? $product->price ?? 0);
    }

    /**
     * Get price from price group if available.
     */
    protected function getPriceGroupPrice(Product $product, ?ProductVariant $variant = null): ?float
    {
        if (!$this->priceGroup) {
            return null;
        }

        // Check for specific price override
        $query = ProductPriceGroupPrice::where('product_id', $product->id)
            ->where('selling_price_group_id', $this->priceGroup->id);

        if ($variant) {
            $query->where('product_variant_id', $variant->id);
        } else {
            $query->whereNull('product_variant_id');
        }

        $priceOverride = $query->first();

        if ($priceOverride) {
            return (float) $priceOverride->price;
        }

        // Calculate price using price group formula
        $basePrice = $this->getBasePrice($product, $variant);
        return $this->priceGroup->calculatePrice($basePrice);
    }

    /**
     * Calculate bulk discount for a product.
     */
    public function getBulkDiscount(
        Product $product,
        float $quantity,
        ?ProductVariant $variant = null
    ): ?array {
        $query = BulkDiscount::active()
            ->ordered()
            ->where(function ($q) use ($product) {
                $q->where('product_id', $product->id)
                  ->orWhere('category_id', $product->category_id)
                  ->orWhere('brand_id', $product->brand_id);
            });

        // Filter by price group if set
        if ($this->priceGroup) {
            $query->where(function ($q) {
                $q->whereNull('selling_price_group_id')
                  ->orWhere('selling_price_group_id', $this->priceGroup->id);
            });
        }

        // Filter by customer group if set
        if ($this->customerGroup) {
            $query->where(function ($q) {
                $q->whereNull('customer_group_id')
                  ->orWhere('customer_group_id', $this->customerGroup->id);
            });
        }

        $discounts = $query->get();

        foreach ($discounts as $discount) {
            if ($discount->isApplicable($quantity)) {
                $priceData = $this->getPrice($product, $variant);
                $discountAmount = $discount->calculateDiscount($priceData['final_price'], $quantity);

                return [
                    'discount_id' => $discount->id,
                    'discount_name' => $discount->name,
                    'discount_type' => $discount->discount_type,
                    'discount_value' => $discount->discount_amount,
                    'total_discount' => round($discountAmount, 2),
                    'min_quantity' => $discount->min_quantity,
                    'max_quantity' => $discount->max_quantity,
                ];
            }
        }

        return null;
    }

    /**
     * Calculate complete pricing for a cart item.
     */
    public function calculateItemPricing(
        Product $product,
        float $quantity,
        ?ProductVariant $variant = null
    ): array {
        $priceData = $this->getPrice($product, $variant);
        $bulkDiscount = $this->getBulkDiscount($product, $quantity, $variant);

        $lineTotal = $priceData['final_price'] * $quantity;
        $discountAmount = $bulkDiscount['total_discount'] ?? 0;
        $finalTotal = $lineTotal - $discountAmount;

        return [
            'unit_price' => $priceData['final_price'],
            'base_price' => $priceData['base_price'],
            'quantity' => $quantity,
            'line_total' => round($lineTotal, 2),
            'discount_amount' => round($discountAmount, 2),
            'final_total' => round($finalTotal, 2),
            'price_group' => $priceData['price_group_name'],
            'customer_group_discount' => $priceData['customer_group_discount'],
            'bulk_discount' => $bulkDiscount,
        ];
    }

    /**
     * Get all applicable bulk discounts for a product (for display).
     */
    public function getAvailableBulkDiscounts(Product $product): array
    {
        return BulkDiscount::active()
            ->ordered()
            ->where(function ($q) use ($product) {
                $q->where('product_id', $product->id)
                  ->orWhere('category_id', $product->category_id)
                  ->orWhere('brand_id', $product->brand_id);
            })
            ->get()
            ->map(fn($d) => [
                'id' => $d->id,
                'name' => $d->name,
                'min_quantity' => $d->min_quantity,
                'max_quantity' => $d->max_quantity,
                'discount_type' => $d->discount_type,
                'discount_amount' => $d->discount_amount,
            ])
            ->toArray();
    }

    /**
     * Get all price group prices for a product.
     */
    public function getAllPriceGroupPrices(Product $product, ?ProductVariant $variant = null): array
    {
        $priceGroups = SellingPriceGroup::active()->ordered()->get();
        $result = [];

        foreach ($priceGroups as $group) {
            $this->setPriceGroup($group);
            $price = $this->getPriceGroupPrice($product, $variant);

            $result[] = [
                'price_group_id' => $group->id,
                'price_group_name' => $group->name,
                'price' => $price ?? $this->getBasePrice($product, $variant),
            ];
        }

        // Reset price group
        $this->priceGroup = null;

        return $result;
    }
}
