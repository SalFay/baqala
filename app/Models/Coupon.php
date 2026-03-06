<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Coupon extends BaseModel
{
    use SoftDeletes;

    const TYPE_FIXED = 'fixed';
    const TYPE_PERCENTAGE = 'percentage';
    const TYPE_FREE_SHIPPING = 'free_shipping';

    const APPLIES_ALL = 'all';
    const APPLIES_CATEGORY = 'category';
    const APPLIES_BRAND = 'brand';
    const APPLIES_PRODUCT = 'product';

    protected $fillable = [
        'store_id',
        'code',
        'name',
        'description',
        'discount_type',
        'discount_amount',
        'applies_to',
        'applies_to_ids',
        'min_order_amount',
        'max_discount_amount',
        'customer_ids',
        'customer_group_ids',
        'first_order_only',
        'max_uses',
        'max_uses_per_customer',
        'current_uses',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'applies_to_ids' => 'array',
        'customer_ids' => 'array',
        'customer_group_ids' => 'array',
        'first_order_only' => 'boolean',
        'max_uses' => 'integer',
        'max_uses_per_customer' => 'integer',
        'current_uses' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Coupon $coupon) {
            if (empty($coupon->code)) {
                $coupon->code = self::generateCode();
            }
            $coupon->code = strtoupper($coupon->code);
        });
    }

    public static function generateCode(int $length = 8): string
    {
        do {
            $code = strtoupper(Str::random($length));
        } while (self::where('code', $code)->exists());

        return $code;
    }

    // ==================== Relationships ====================

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
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

    public function scopeByCode($query, string $code)
    {
        return $query->where('code', strtoupper($code));
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

    /**
     * Validate coupon for a specific customer and order
     */
    public function validateForCustomer(?Customer $customer, float $orderTotal): array
    {
        $errors = [];

        if (!$this->isValid()) {
            $errors[] = 'This coupon is no longer valid';
            return $errors;
        }

        // Check minimum order amount
        if ($this->min_order_amount && $orderTotal < $this->min_order_amount) {
            $errors[] = "Minimum order amount is \${$this->min_order_amount}";
        }

        // Check if coupon is for specific customers
        if ($this->customer_ids && !empty($this->customer_ids)) {
            if (!$customer || !in_array($customer->id, $this->customer_ids)) {
                $errors[] = 'This coupon is not available for your account';
            }
        }

        // Check customer group
        if ($this->customer_group_ids && !empty($this->customer_group_ids)) {
            if (!$customer || !in_array($customer->customer_group_id, $this->customer_group_ids)) {
                $errors[] = 'This coupon is not available for your customer group';
            }
        }

        // Check first order only
        if ($this->first_order_only && $customer) {
            $previousOrders = Order::where('customer_id', $customer->id)
                ->where('status', '!=', 'cancelled')
                ->count();
            if ($previousOrders > 0) {
                $errors[] = 'This coupon is only valid for first-time orders';
            }
        }

        // Check per-customer usage limit
        if ($this->max_uses_per_customer && $customer) {
            $customerUsage = $this->usages()->where('customer_id', $customer->id)->count();
            if ($customerUsage >= $this->max_uses_per_customer) {
                $errors[] = 'You have already used this coupon the maximum number of times';
            }
        }

        return $errors;
    }

    /**
     * Calculate discount for a given amount
     */
    public function calculateDiscount(float $amount): float
    {
        if ($this->discount_type === self::TYPE_FREE_SHIPPING) {
            return 0; // Shipping discount handled separately
        }

        $discount = 0;

        if ($this->discount_type === self::TYPE_PERCENTAGE) {
            $discount = round($amount * ($this->discount_amount / 100), 2);
        } else {
            $discount = min($this->discount_amount, $amount);
        }

        // Apply max discount cap if set
        if ($this->max_discount_amount && $discount > $this->max_discount_amount) {
            $discount = $this->max_discount_amount;
        }

        return $discount;
    }

    /**
     * Check if coupon provides free shipping
     */
    public function isFreeShipping(): bool
    {
        return $this->discount_type === self::TYPE_FREE_SHIPPING;
    }

    public function isPercentage(): bool
    {
        return $this->discount_type === self::TYPE_PERCENTAGE;
    }

    /**
     * Record coupon usage
     */
    public function recordUsage(?int $customerId, ?int $orderId, float $discountApplied): CouponUsage
    {
        $this->increment('current_uses');

        return $this->usages()->create([
            'customer_id' => $customerId,
            'order_id' => $orderId,
            'discount_applied' => $discountApplied,
        ]);
    }

    /**
     * Get display text for discount
     */
    public function getDiscountDisplayAttribute(): string
    {
        if ($this->isFreeShipping()) {
            return 'Free Shipping';
        }

        if ($this->isPercentage()) {
            $text = "{$this->discount_amount}% off";
            if ($this->max_discount_amount) {
                $text .= " (max \${$this->max_discount_amount})";
            }
            return $text;
        }

        return '$' . number_format($this->discount_amount, 2) . ' off';
    }
}
