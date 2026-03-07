<?php

namespace App\Services\Promotion;

use App\Models\BulkDiscount;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\DiscountRule;
use App\Models\Product;
use App\Models\TimePricing;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * PromotionEngine - Centralized discount calculation engine
 *
 * Evaluates all discount types in priority order:
 * 1. Time-based pricing (highest priority - special hours)
 * 2. Loyalty tier discounts (membership benefits)
 * 3. Customer group discounts
 * 4. Bulk/volume discounts (quantity-based)
 * 5. Promotional rules (conditional discounts)
 * 6. Coupons (code-based, applied last)
 */
class PromotionEngine
{
    protected array $config;
    protected ?string $appliedCouponCode = null;

    public function __construct()
    {
        $this->config = [
            'max_discount_percentage' => config('pos.max_discount_percentage', 50),
            'allow_stacking' => config('pos.allow_discount_stacking', true),
            'loyalty_tier_stacks' => config('pos.loyalty_tier_stacks_with_other', true),
        ];
    }

    /**
     * Calculate all applicable discounts for a cart
     */
    public function calculateCartDiscounts(Cart $cart, ?string $couponCode = null, ?string $paymentMethod = null): DiscountResult
    {
        $context = DiscountContext::fromCart($cart, $paymentMethod);
        return $this->calculate($context, $couponCode);
    }

    /**
     * Calculate discounts for a single product (used in POS item display)
     */
    public function calculateProductDiscount(
        Product $product,
        int $quantity = 1,
        ?int $customerId = null,
        ?int $storeId = null
    ): DiscountResult {
        $customer = $customerId ? \App\Models\Customer::find($customerId) : null;
        $store = $storeId ? \App\Models\Store::find($storeId) : \App\Models\Store::first();

        $context = DiscountContext::forProduct($product, $quantity, $customer, $store);
        return $this->calculate($context);
    }

    /**
     * Main calculation method
     */
    public function calculate(DiscountContext $context, ?string $couponCode = null): DiscountResult
    {
        $appliedDiscounts = collect();
        $freeItems = [];
        $itemDiscounts = [];
        $warnings = [];
        $hasFreeShipping = false;

        // 1. Time-based pricing discounts
        $timeDiscounts = $this->evaluateTimePricing($context);
        $appliedDiscounts = $appliedDiscounts->merge($timeDiscounts);

        // 2. Loyalty tier discounts (if customer has a tier)
        if ($context->loyaltyTier && $context->loyaltyTier->discount_percentage > 0) {
            $tierDiscount = $this->evaluateLoyaltyTier($context);
            if ($tierDiscount) {
                $appliedDiscounts->push($tierDiscount);
            }
        }

        // 3. Customer group discounts
        if ($context->customerGroupId) {
            $groupDiscount = $this->evaluateCustomerGroup($context);
            if ($groupDiscount) {
                $appliedDiscounts->push($groupDiscount);
            }
        }

        // 4. Bulk/volume discounts (per-item)
        $bulkDiscounts = $this->evaluateBulkDiscounts($context);
        $appliedDiscounts = $appliedDiscounts->merge($bulkDiscounts);

        // 5. Promotional rules
        $ruleResult = $this->evaluateDiscountRules($context);
        $appliedDiscounts = $appliedDiscounts->merge($ruleResult['discounts']);
        $freeItems = array_merge($freeItems, $ruleResult['free_items']);

        // 6. Coupon (if provided)
        if ($couponCode) {
            $couponResult = $this->evaluateCoupon($couponCode, $context);
            if ($couponResult['discount']) {
                $appliedDiscounts->push($couponResult['discount']);
                $hasFreeShipping = $couponResult['free_shipping'];
            }
            if ($couponResult['warning']) {
                $warnings[] = $couponResult['warning'];
            }
        }

        // Apply stacking rules and cap total discount
        $appliedDiscounts = $this->applyStackingRules($appliedDiscounts);
        $appliedDiscounts = $this->capTotalDiscount($appliedDiscounts, $context->subtotal);

        return new DiscountResult(
            appliedDiscounts: $appliedDiscounts,
            originalTotal: $context->subtotal,
            hasFreeShipping: $hasFreeShipping,
            freeItems: $freeItems,
            itemDiscounts: $itemDiscounts,
            warnings: $warnings
        );
    }

    /**
     * Evaluate time-based pricing rules
     */
    protected function evaluateTimePricing(DiscountContext $context): Collection
    {
        $discounts = collect();

        $timePricings = TimePricing::query()
            ->active()
            ->where(function ($q) use ($context) {
                $q->whereNull('store_id')
                  ->orWhere('store_id', $context->store?->id);
            })
            ->get();

        foreach ($timePricings as $pricing) {
            if (!$pricing->isActiveNow()) {
                continue;
            }

            // Check if pricing applies to any item in context
            foreach ($context->items as $item) {
                $productId = $item['product_id'] ?? $item->product_id ?? null;

                if ($pricing->appliesToProduct($productId, $item['category_id'] ?? null)) {
                    $amount = $this->calculateTimePricingDiscount($pricing, $item);

                    if ($amount > 0) {
                        $discounts->push(new AppliedDiscount(
                            type: AppliedDiscount::TYPE_TIME_BASED,
                            sourceId: $pricing->id,
                            sourceName: $pricing->name,
                            discountType: $pricing->discount_type,
                            discountValue: $pricing->discount_value,
                            discountAmount: $amount,
                            originalAmount: $item['line_total'] ?? ($item['unit_price'] * $item['quantity']),
                            priority: 100,
                            isStackable: false,
                            appliesToItemId: $productId,
                            conditionsMet: ['time_range' => true, 'day_of_week' => true],
                        ));
                    }
                }
            }
        }

        return $discounts;
    }

    protected function calculateTimePricingDiscount($pricing, $item): float
    {
        $lineTotal = $item['line_total'] ?? ($item['unit_price'] * $item['quantity']);

        if ($pricing->discount_type === 'percentage') {
            return $lineTotal * ($pricing->discount_value / 100);
        }

        return min($pricing->discount_value * ($item['quantity'] ?? 1), $lineTotal);
    }

    /**
     * Evaluate loyalty tier discount
     */
    protected function evaluateLoyaltyTier(DiscountContext $context): ?AppliedDiscount
    {
        $tier = $context->loyaltyTier;
        if (!$tier || $tier->discount_percentage <= 0) {
            return null;
        }

        $discountAmount = $context->subtotal * ($tier->discount_percentage / 100);

        return new AppliedDiscount(
            type: AppliedDiscount::TYPE_LOYALTY_TIER,
            sourceId: $tier->id,
            sourceName: $tier->name . ' Member',
            discountType: 'percentage',
            discountValue: $tier->discount_percentage,
            discountAmount: $discountAmount,
            originalAmount: $context->subtotal,
            priority: 90,
            isStackable: $this->config['loyalty_tier_stacks'],
            conditionsMet: ['tier_active' => true],
        );
    }

    /**
     * Evaluate customer group discount
     */
    protected function evaluateCustomerGroup(DiscountContext $context): ?AppliedDiscount
    {
        if (!$context->customer) {
            return null;
        }

        $group = $context->customer->customerGroup;
        if (!$group || !$group->discount_percent || $group->discount_percent <= 0) {
            return null;
        }

        $discountAmount = $context->subtotal * ($group->discount_percent / 100);

        return new AppliedDiscount(
            type: AppliedDiscount::TYPE_CUSTOMER_GROUP,
            sourceId: $group->id,
            sourceName: $group->name . ' Discount',
            discountType: 'percentage',
            discountValue: $group->discount_percent,
            discountAmount: $discountAmount,
            originalAmount: $context->subtotal,
            priority: 80,
            isStackable: true,
            conditionsMet: ['customer_in_group' => true],
        );
    }

    /**
     * Evaluate bulk/volume discounts
     */
    protected function evaluateBulkDiscounts(DiscountContext $context): Collection
    {
        $discounts = collect();

        foreach ($context->items as $item) {
            $productId = $item['product_id'] ?? $item->product_id ?? null;
            $categoryId = $item['category_id'] ?? null;
            $quantity = $item['quantity'] ?? 1;
            $unitPrice = $item['unit_price'] ?? 0;

            if (!$productId) {
                continue;
            }

            $bulkDiscount = BulkDiscount::query()
                ->active()
                ->ordered()
                ->where(function ($q) use ($productId, $categoryId) {
                    $q->where('product_id', $productId)
                      ->orWhere('category_id', $categoryId)
                      ->orWhereNull('product_id');
                })
                ->where('min_quantity', '<=', $quantity)
                ->where(function ($q) use ($quantity) {
                    $q->whereNull('max_quantity')
                      ->orWhere('max_quantity', '>=', $quantity);
                })
                ->first();

            if (!$bulkDiscount) {
                continue;
            }

            $discountAmount = $bulkDiscount->calculateDiscount($unitPrice, $quantity);

            $discounts->push(new AppliedDiscount(
                type: AppliedDiscount::TYPE_BULK,
                sourceId: $bulkDiscount->id,
                sourceName: 'Volume Discount',
                discountType: $bulkDiscount->discount_type,
                discountValue: $bulkDiscount->discount_amount,
                discountAmount: $discountAmount,
                originalAmount: $unitPrice * $quantity,
                priority: 70,
                isStackable: true,
                appliesToItemId: $productId,
                conditionsMet: ['min_quantity' => $quantity],
            ));
        }

        return $discounts;
    }

    /**
     * Evaluate discount rules
     */
    protected function evaluateDiscountRules(DiscountContext $context): array
    {
        $discounts = collect();
        $freeItems = [];

        $rules = DiscountRule::query()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('max_uses')->orWhereColumn('current_uses', '<', 'max_uses');
            })
            ->orderByDesc('priority')
            ->get();

        $stopFurtherRules = false;

        foreach ($rules as $rule) {
            if ($stopFurtherRules && !$rule->is_stackable) {
                continue;
            }

            if (!$this->ruleMatchesContext($rule, $context)) {
                continue;
            }

            $discountAmount = $this->calculateRuleDiscount($rule, $context);

            if ($discountAmount > 0) {
                $discounts->push(new AppliedDiscount(
                    type: AppliedDiscount::TYPE_RULE,
                    sourceId: $rule->id,
                    sourceName: $rule->name ?? 'Promotional Discount',
                    discountType: $rule->discount_type,
                    discountValue: $rule->discount_amount,
                    discountAmount: $discountAmount,
                    originalAmount: $context->subtotal,
                    priority: $rule->priority,
                    isStackable: $rule->is_stackable,
                    conditionsMet: $this->getMatchedConditions($rule, $context),
                ));

                if ($rule->stop_further_rules) {
                    $stopFurtherRules = true;
                }
            }

            // Handle BOGO rules
            if ($rule->discount_type === 'bogo' && isset($rule->conditions['bogo_config'])) {
                $bogoItems = $this->evaluateBogoRule($rule, $context);
                $freeItems = array_merge($freeItems, $bogoItems);
            }
        }

        return [
            'discounts' => $discounts,
            'free_items' => $freeItems,
        ];
    }

    protected function ruleMatchesContext(DiscountRule $rule, DiscountContext $context): bool
    {
        $conditions = $rule->conditions ?? [];

        // Check minimum quantity
        if (isset($conditions['min_quantity']) && $context->totalQuantity < $conditions['min_quantity']) {
            return false;
        }

        // Check minimum total
        if (isset($conditions['min_total']) && $context->subtotal < $conditions['min_total']) {
            return false;
        }

        // Check customer group
        if (!empty($conditions['customer_group_ids'])) {
            if (!$context->customerGroupId || !in_array($context->customerGroupId, $conditions['customer_group_ids'])) {
                return false;
            }
        }

        // Check payment method
        if (!empty($conditions['payment_method_ids']) && $context->paymentMethod) {
            if (!in_array($context->paymentMethod, $conditions['payment_method_ids'])) {
                return false;
            }
        }

        // Check day of week
        if (!empty($conditions['days_of_week'])) {
            if (!in_array($context->dayOfWeek, $conditions['days_of_week'])) {
                return false;
            }
        }

        // Check time range
        if (!empty($conditions['time_range'])) {
            $start = $conditions['time_range']['start'] ?? '00:00';
            $end = $conditions['time_range']['end'] ?? '23:59';
            if (!$context->isWithinTimeRange($start, $end)) {
                return false;
            }
        }

        // Check applies_to scope
        if ($rule->applies_to !== 'all') {
            $targetIds = $rule->applies_to_ids ?? [];

            switch ($rule->applies_to) {
                case 'category':
                    if (empty(array_intersect($context->categoryIds, $targetIds))) {
                        return false;
                    }
                    break;
                case 'brand':
                    if (empty(array_intersect($context->brandIds, $targetIds))) {
                        return false;
                    }
                    break;
                case 'product':
                    if (empty(array_intersect($context->productIds, $targetIds))) {
                        return false;
                    }
                    break;
                case 'customer_group':
                    if (!$context->customerGroupId || !in_array($context->customerGroupId, $targetIds)) {
                        return false;
                    }
                    break;
            }
        }

        return true;
    }

    protected function calculateRuleDiscount(DiscountRule $rule, DiscountContext $context): float
    {
        $baseAmount = $context->subtotal;

        // If rule targets specific items, only use their total
        if ($rule->applies_to !== 'all' && !empty($rule->applies_to_ids)) {
            $baseAmount = collect($context->items)
                ->filter(function ($item) use ($rule) {
                    $productId = $item['product_id'] ?? $item->product_id ?? null;
                    $categoryId = $item['category_id'] ?? null;
                    $brandId = $item['brand_id'] ?? null;

                    return match ($rule->applies_to) {
                        'product' => in_array($productId, $rule->applies_to_ids),
                        'category' => in_array($categoryId, $rule->applies_to_ids),
                        'brand' => in_array($brandId, $rule->applies_to_ids),
                        default => true,
                    };
                })
                ->sum(fn($item) => $item['line_total'] ?? ($item['unit_price'] * $item['quantity']));
        }

        if ($rule->discount_type === 'percentage') {
            return $baseAmount * ($rule->discount_amount / 100);
        }

        return min($rule->discount_amount, $baseAmount);
    }

    protected function getMatchedConditions(DiscountRule $rule, DiscountContext $context): array
    {
        $matched = [];
        $conditions = $rule->conditions ?? [];

        if (isset($conditions['min_quantity'])) {
            $matched['min_quantity'] = $context->totalQuantity;
        }
        if (isset($conditions['min_total'])) {
            $matched['min_total'] = $context->subtotal;
        }
        if (!empty($conditions['days_of_week'])) {
            $matched['day_of_week'] = $context->dayOfWeek;
        }
        if (!empty($conditions['time_range'])) {
            $matched['time'] = $context->time;
        }

        return $matched;
    }

    protected function evaluateBogoRule(DiscountRule $rule, DiscountContext $context): array
    {
        // BOGO: Buy X Get Y Free
        $config = $rule->conditions['bogo_config'] ?? [];
        $buyQuantity = $config['buy_quantity'] ?? 1;
        $getQuantity = $config['get_quantity'] ?? 1;
        $getProductId = $config['get_product_id'] ?? null;

        $freeItems = [];

        foreach ($context->items as $item) {
            $productId = $item['product_id'] ?? $item->product_id ?? null;
            $quantity = $item['quantity'] ?? 1;

            // How many sets of BOGO can be applied
            $sets = floor($quantity / $buyQuantity);

            if ($sets > 0) {
                $freeItems[] = [
                    'product_id' => $getProductId ?? $productId,
                    'quantity' => $sets * $getQuantity,
                    'rule_id' => $rule->id,
                    'reason' => "Buy {$buyQuantity} Get {$getQuantity} Free",
                ];
            }
        }

        return $freeItems;
    }

    /**
     * Evaluate coupon code
     */
    protected function evaluateCoupon(string $code, DiscountContext $context): array
    {
        $result = [
            'discount' => null,
            'free_shipping' => false,
            'warning' => null,
        ];

        $coupon = Coupon::where('code', strtoupper(trim($code)))->first();

        if (!$coupon) {
            $result['warning'] = 'Invalid coupon code';
            return $result;
        }

        // Validate coupon
        $errors = $coupon->validateForCustomer($context->customer, $context->subtotal);

        if (!empty($errors)) {
            $result['warning'] = implode('. ', $errors);
            return $result;
        }

        // Calculate discount
        if ($coupon->isFreeShipping()) {
            $result['free_shipping'] = true;
            $result['discount'] = new AppliedDiscount(
                type: AppliedDiscount::TYPE_COUPON,
                sourceId: $coupon->id,
                sourceName: "Coupon: {$coupon->code}",
                discountType: 'free_shipping',
                discountValue: 0,
                discountAmount: 0,
                originalAmount: 0,
                priority: 10,
                isStackable: true,
                conditionsMet: ['code' => $code],
                description: 'Free Shipping'
            );
        } else {
            $discountAmount = $coupon->calculateDiscount($context->subtotal);

            $result['discount'] = new AppliedDiscount(
                type: AppliedDiscount::TYPE_COUPON,
                sourceId: $coupon->id,
                sourceName: "Coupon: {$coupon->code}",
                discountType: $coupon->discount_type,
                discountValue: $coupon->discount_amount,
                discountAmount: $discountAmount,
                originalAmount: $context->subtotal,
                priority: 10,
                isStackable: true,
                conditionsMet: ['code' => $code],
            );
        }

        $this->appliedCouponCode = $coupon->code;

        return $result;
    }

    /**
     * Apply stacking rules - remove non-stackable discounts if others exist
     */
    protected function applyStackingRules(Collection $discounts): Collection
    {
        if (!$this->config['allow_stacking']) {
            // Keep only highest discount
            return collect([$discounts->sortByDesc('discountAmount')->first()])->filter();
        }

        // Group by stackable vs non-stackable
        $stackable = $discounts->filter(fn($d) => $d->isStackable);
        $nonStackable = $discounts->filter(fn($d) => !$d->isStackable);

        if ($nonStackable->isEmpty()) {
            return $stackable;
        }

        // Keep highest non-stackable + all stackable
        $highestNonStackable = $nonStackable->sortByDesc('priority')->first();

        return $stackable->push($highestNonStackable);
    }

    /**
     * Cap total discount to max percentage of order
     */
    protected function capTotalDiscount(Collection $discounts, float $subtotal): Collection
    {
        $maxDiscount = $subtotal * ($this->config['max_discount_percentage'] / 100);
        $totalDiscount = $discounts->sum('discountAmount');

        if ($totalDiscount <= $maxDiscount) {
            return $discounts;
        }

        // Scale down all discounts proportionally
        $scaleFactor = $maxDiscount / $totalDiscount;

        return $discounts->map(function ($discount) use ($scaleFactor) {
            return new AppliedDiscount(
                type: $discount->type,
                sourceId: $discount->sourceId,
                sourceName: $discount->sourceName,
                discountType: $discount->discountType,
                discountValue: $discount->discountValue,
                discountAmount: $discount->discountAmount * $scaleFactor,
                originalAmount: $discount->originalAmount,
                priority: $discount->priority,
                isStackable: $discount->isStackable,
                appliesToItemId: $discount->appliesToItemId,
                conditionsMet: $discount->conditionsMet,
                description: $discount->description . ' (capped)',
            );
        });
    }

    /**
     * Preview all available discounts for display
     */
    public function previewAvailableDiscounts(DiscountContext $context): array
    {
        $available = [];

        // Bulk discounts that could apply with more quantity
        $bulkDiscounts = BulkDiscount::active()->ordered()->get();
        foreach ($bulkDiscounts as $bulk) {
            $available[] = [
                'type' => 'bulk',
                'name' => "Buy {$bulk->min_quantity}+ and save",
                'discount' => $bulk->discount_type === 'percentage'
                    ? "{$bulk->discount_amount}%"
                    : number_format($bulk->discount_amount, 2),
                'requirement' => "Minimum {$bulk->min_quantity} items",
                'met' => $context->totalQuantity >= $bulk->min_quantity,
            ];
        }

        // Active promotional rules
        $rules = DiscountRule::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->get();

        foreach ($rules as $rule) {
            $conditions = $rule->conditions ?? [];
            $available[] = [
                'type' => 'rule',
                'name' => $rule->name ?? 'Special Offer',
                'discount' => $rule->discount_type === 'percentage'
                    ? "{$rule->discount_amount}%"
                    : number_format($rule->discount_amount, 2),
                'requirement' => $this->describeConditions($conditions),
                'met' => $this->ruleMatchesContext($rule, $context),
            ];
        }

        return $available;
    }

    protected function describeConditions(array $conditions): string
    {
        $parts = [];

        if (isset($conditions['min_quantity'])) {
            $parts[] = "Min {$conditions['min_quantity']} items";
        }
        if (isset($conditions['min_total'])) {
            $parts[] = "Min order " . number_format($conditions['min_total'], 2);
        }
        if (!empty($conditions['days_of_week'])) {
            $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            $selectedDays = array_map(fn($d) => $days[$d - 1] ?? '', $conditions['days_of_week']);
            $parts[] = implode(', ', $selectedDays);
        }
        if (!empty($conditions['time_range'])) {
            $parts[] = "{$conditions['time_range']['start']} - {$conditions['time_range']['end']}";
        }

        return implode(' | ', $parts) ?: 'No restrictions';
    }

    /**
     * Record usage of applied discounts
     */
    public function recordUsage(DiscountResult $result, int $orderId, ?int $customerId = null): void
    {
        foreach ($result->appliedDiscounts as $discount) {
            match ($discount->type) {
                AppliedDiscount::TYPE_RULE => $this->recordRuleUsage($discount->sourceId, $customerId),
                AppliedDiscount::TYPE_COUPON => $this->recordCouponUsage($discount->sourceId, $customerId, $orderId, $discount->discountAmount),
                default => null,
            };
        }
    }

    protected function recordRuleUsage(int $ruleId, ?int $customerId): void
    {
        DiscountRule::where('id', $ruleId)->increment('current_uses');
    }

    protected function recordCouponUsage(int $couponId, ?int $customerId, int $orderId, float $discountAmount): void
    {
        $coupon = Coupon::find($couponId);
        if ($coupon) {
            $coupon->recordUsage($customerId, $orderId, $discountAmount);
        }
    }
}
