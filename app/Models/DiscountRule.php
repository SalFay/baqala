<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiscountRule extends BaseModel
{
    use SoftDeletes;

    const TYPE_FIXED = 'fixed';
    const TYPE_PERCENTAGE = 'percentage';

    const APPLIES_ALL = 'all';
    const APPLIES_CATEGORY = 'category';
    const APPLIES_BRAND = 'brand';
    const APPLIES_PRODUCT = 'product';
    const APPLIES_CUSTOMER_GROUP = 'customer_group';

    protected $fillable = [
        'store_id',
        'name',
        'description',
        'discount_type',
        'discount_amount',
        'applies_to',
        'applies_to_ids',
        'conditions',
        'priority',
        'is_stackable',
        'stop_further_rules',
        'max_uses',
        'max_uses_per_customer',
        'current_uses',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'applies_to_ids' => 'array',
        'conditions' => 'array',
        'priority' => 'integer',
        'is_stackable' => 'boolean',
        'stop_further_rules' => 'boolean',
        'max_uses' => 'integer',
        'max_uses_per_customer' => 'integer',
        'current_uses' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // ==================== Relationships ====================

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    // ==================== Scopes ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeValid($query)
    {
        $now = now();
        return $query->active()
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            })
            ->where(function ($q) {
                $q->whereNull('max_uses')->orWhereColumn('current_uses', '<', 'max_uses');
            });
    }

    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'desc');
    }

    public function scopeForProduct($query, int $productId)
    {
        return $query->where(function ($q) use ($productId) {
            $q->where('applies_to', self::APPLIES_ALL)
                ->orWhere(function ($q) use ($productId) {
                    $q->where('applies_to', self::APPLIES_PRODUCT)
                        ->whereJsonContains('applies_to_ids', $productId);
                });
        });
    }

    public function scopeForCategory($query, int $categoryId)
    {
        return $query->where(function ($q) use ($categoryId) {
            $q->where('applies_to', self::APPLIES_ALL)
                ->orWhere(function ($q) use ($categoryId) {
                    $q->where('applies_to', self::APPLIES_CATEGORY)
                        ->whereJsonContains('applies_to_ids', $categoryId);
                });
        });
    }

    // ==================== Helper Methods ====================

    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        if ($this->starts_at && $this->starts_at > $now) {
            return false;
        }

        if ($this->ends_at && $this->ends_at < $now) {
            return false;
        }

        if ($this->max_uses !== null && $this->current_uses >= $this->max_uses) {
            return false;
        }

        return true;
    }

    public function isPercentage(): bool
    {
        return $this->discount_type === self::TYPE_PERCENTAGE;
    }

    public function isFixed(): bool
    {
        return $this->discount_type === self::TYPE_FIXED;
    }

    /**
     * Calculate discount for a given amount
     */
    public function calculateDiscount(float $amount): float
    {
        if ($this->isPercentage()) {
            return round($amount * ($this->discount_amount / 100), 2);
        }

        return min($this->discount_amount, $amount);
    }

    /**
     * Check if this rule applies to a specific product
     */
    public function appliesToProduct(Product $product): bool
    {
        if ($this->applies_to === self::APPLIES_ALL) {
            return true;
        }

        if ($this->applies_to === self::APPLIES_PRODUCT) {
            return in_array($product->id, $this->applies_to_ids ?? []);
        }

        if ($this->applies_to === self::APPLIES_CATEGORY && $product->category_id) {
            return in_array($product->category_id, $this->applies_to_ids ?? []);
        }

        return false;
    }

    /**
     * Check if conditions are met
     */
    public function checkConditions(array $context = []): bool
    {
        if (!$this->conditions) {
            return true;
        }

        $conditions = $this->conditions;

        // Minimum quantity
        if (isset($conditions['min_quantity']) && isset($context['quantity'])) {
            if ($context['quantity'] < $conditions['min_quantity']) {
                return false;
            }
        }

        // Minimum total
        if (isset($conditions['min_total']) && isset($context['total'])) {
            if ($context['total'] < $conditions['min_total']) {
                return false;
            }
        }

        // Customer group
        if (isset($conditions['customer_group_ids']) && isset($context['customer_group_id'])) {
            if (!in_array($context['customer_group_id'], $conditions['customer_group_ids'])) {
                return false;
            }
        }

        // Payment method
        if (isset($conditions['payment_method_ids']) && isset($context['payment_method_id'])) {
            if (!in_array($context['payment_method_id'], $conditions['payment_method_ids'])) {
                return false;
            }
        }

        // Days of week (1 = Monday, 7 = Sunday)
        if (isset($conditions['days_of_week'])) {
            $today = now()->dayOfWeekIso;
            if (!in_array($today, $conditions['days_of_week'])) {
                return false;
            }
        }

        // Time range
        if (isset($conditions['time_range'])) {
            $now = now()->format('H:i');
            $start = $conditions['time_range']['start'];
            $end = $conditions['time_range']['end'];
            if ($now < $start || $now > $end) {
                return false;
            }
        }

        return true;
    }

    /**
     * Increment usage counter
     */
    public function incrementUsage(): bool
    {
        $this->current_uses++;
        return $this->save();
    }

    /**
     * Get display text for discount
     */
    public function getDiscountDisplayAttribute(): string
    {
        if ($this->isPercentage()) {
            return "{$this->discount_amount}%";
        }

        return '$' . number_format($this->discount_amount, 2);
    }
}
