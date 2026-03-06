<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Customer;
use App\Models\DiscountRule;
use App\Models\Product;
use Illuminate\Support\Collection;

class DiscountService
{
    /**
     * Get all applicable discount rules for given context
     */
    public function getApplicableRules(array $context = []): Collection
    {
        $query = DiscountRule::valid()->byPriority();

        // Filter by product if provided
        if (isset($context['product_id'])) {
            $query->forProduct($context['product_id']);
        }

        // Filter by category if provided
        if (isset($context['category_id'])) {
            $query->forCategory($context['category_id']);
        }

        return $query->get()->filter(fn($rule) => $rule->checkConditions($context));
    }

    /**
     * Calculate total discount for cart items
     */
    public function calculateCartDiscount(array $items, array $context = []): array
    {
        $totalDiscount = 0;
        $appliedRules = [];
        $itemDiscounts = [];

        // Get all valid rules ordered by priority
        $rules = DiscountRule::valid()->byPriority()->get();

        foreach ($items as $index => $item) {
            $itemDiscounts[$index] = [
                'original_price' => $item['price'],
                'discounted_price' => $item['price'],
                'discount_amount' => 0,
                'applied_rules' => [],
            ];

            $itemContext = array_merge($context, [
                'product_id' => $item['product_id'] ?? null,
                'category_id' => $item['category_id'] ?? null,
                'quantity' => $item['quantity'] ?? 1,
                'total' => ($item['price'] ?? 0) * ($item['quantity'] ?? 1),
            ]);

            $itemTotal = $item['price'] * $item['quantity'];
            $remainingAmount = $itemTotal;

            foreach ($rules as $rule) {
                // Check if rule applies to this product
                if (!$this->ruleAppliesToItem($rule, $item)) {
                    continue;
                }

                // Check conditions
                if (!$rule->checkConditions($itemContext)) {
                    continue;
                }

                // Calculate discount
                $discount = $rule->calculateDiscount($remainingAmount);

                if ($discount > 0) {
                    $itemDiscounts[$index]['discount_amount'] += $discount;
                    $itemDiscounts[$index]['applied_rules'][] = [
                        'id' => $rule->id,
                        'name' => $rule->name,
                        'discount' => $discount,
                    ];

                    $totalDiscount += $discount;
                    $appliedRules[] = $rule;

                    // If not stackable, stop processing for this item
                    if (!$rule->is_stackable) {
                        break;
                    }

                    // Update remaining amount for stacked discounts
                    $remainingAmount -= $discount;

                    // Stop if this rule says so
                    if ($rule->stop_further_rules) {
                        break;
                    }
                }
            }

            $itemDiscounts[$index]['discounted_price'] = max(0, $itemTotal - $itemDiscounts[$index]['discount_amount']) / $item['quantity'];
        }

        return [
            'total_discount' => $totalDiscount,
            'applied_rules' => collect($appliedRules)->unique('id')->values(),
            'item_discounts' => $itemDiscounts,
        ];
    }

    /**
     * Check if a rule applies to a specific item
     */
    protected function ruleAppliesToItem(DiscountRule $rule, array $item): bool
    {
        if ($rule->applies_to === DiscountRule::APPLIES_ALL) {
            return true;
        }

        $ids = $rule->applies_to_ids ?? [];

        switch ($rule->applies_to) {
            case DiscountRule::APPLIES_PRODUCT:
                return in_array($item['product_id'] ?? 0, $ids);

            case DiscountRule::APPLIES_CATEGORY:
                return in_array($item['category_id'] ?? 0, $ids);

            case DiscountRule::APPLIES_BRAND:
                return in_array($item['brand_id'] ?? 0, $ids);

            default:
                return false;
        }
    }

    /**
     * Validate and apply a coupon code
     */
    public function applyCoupon(string $code, array $items, ?Customer $customer = null): array
    {
        $coupon = Coupon::byCode($code)->first();

        if (!$coupon) {
            return [
                'success' => false,
                'error' => 'Invalid coupon code',
            ];
        }

        // Calculate order total for validation
        $orderTotal = collect($items)->sum(fn($item) => ($item['price'] ?? 0) * ($item['quantity'] ?? 1));

        // Validate coupon
        $errors = $coupon->validateForCustomer($customer, $orderTotal);

        if (!empty($errors)) {
            return [
                'success' => false,
                'error' => $errors[0],
            ];
        }

        // Calculate applicable amount (may be filtered by applies_to)
        $applicableAmount = $this->calculateApplicableAmount($coupon, $items);

        // Calculate discount
        $discount = $coupon->calculateDiscount($applicableAmount);

        return [
            'success' => true,
            'coupon' => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'name' => $coupon->name,
                'discount_type' => $coupon->discount_type,
                'discount_amount' => $coupon->discount_amount,
            ],
            'discount' => $discount,
            'free_shipping' => $coupon->isFreeShipping(),
        ];
    }

    /**
     * Calculate the amount that the coupon applies to
     */
    protected function calculateApplicableAmount(Coupon $coupon, array $items): float
    {
        if ($coupon->applies_to === Coupon::APPLIES_ALL) {
            return collect($items)->sum(fn($item) => ($item['price'] ?? 0) * ($item['quantity'] ?? 1));
        }

        $ids = $coupon->applies_to_ids ?? [];

        return collect($items)->filter(function ($item) use ($coupon, $ids) {
            switch ($coupon->applies_to) {
                case Coupon::APPLIES_PRODUCT:
                    return in_array($item['product_id'] ?? 0, $ids);
                case Coupon::APPLIES_CATEGORY:
                    return in_array($item['category_id'] ?? 0, $ids);
                case Coupon::APPLIES_BRAND:
                    return in_array($item['brand_id'] ?? 0, $ids);
                default:
                    return false;
            }
        })->sum(fn($item) => ($item['price'] ?? 0) * ($item['quantity'] ?? 1));
    }

    /**
     * Get best price for a product considering all applicable discounts
     */
    public function getBestPrice(Product $product, array $context = []): array
    {
        $basePrice = $product->sale_price;
        $bestPrice = $basePrice;
        $appliedRule = null;

        $context['product_id'] = $product->id;
        $context['category_id'] = $product->category_id;

        $rules = $this->getApplicableRules($context);

        foreach ($rules as $rule) {
            $discount = $rule->calculateDiscount($basePrice);
            $discountedPrice = $basePrice - $discount;

            if ($discountedPrice < $bestPrice) {
                $bestPrice = $discountedPrice;
                $appliedRule = $rule;
            }
        }

        return [
            'original_price' => $basePrice,
            'discounted_price' => $bestPrice,
            'discount_amount' => $basePrice - $bestPrice,
            'has_discount' => $bestPrice < $basePrice,
            'applied_rule' => $appliedRule ? [
                'id' => $appliedRule->id,
                'name' => $appliedRule->name,
            ] : null,
        ];
    }

    /**
     * Record that discount rules were used in an order
     */
    public function recordRuleUsage(array $ruleIds): void
    {
        DiscountRule::whereIn('id', $ruleIds)->increment('current_uses');
    }

    /**
     * Record coupon usage
     */
    public function recordCouponUsage(Coupon $coupon, ?int $customerId, ?int $orderId, float $discountApplied): void
    {
        $coupon->recordUsage($customerId, $orderId, $discountApplied);
    }
}
