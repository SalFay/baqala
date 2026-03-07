<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class TimePricing extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_id',
        'name',
        'name_ar',
        'description',
        'discount_type',
        'discount_value',
        'applies_to',
        'product_ids',
        'category_ids',
        'brand_ids',
        'days_of_week',
        'start_time',
        'end_time',
        'starts_at',
        'ends_at',
        'priority',
        'is_active',
    ];

    protected $casts = [
        'product_ids' => 'array',
        'category_ids' => 'array',
        'brand_ids' => 'array',
        'days_of_week' => 'array',
        'discount_value' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // Applies to types
    public const APPLIES_TO_ALL = 'all';
    public const APPLIES_TO_PRODUCTS = 'products';
    public const APPLIES_TO_CATEGORIES = 'categories';
    public const APPLIES_TO_BRANDS = 'brands';

    // Discount types
    public const TYPE_PERCENTAGE = 'percentage';
    public const TYPE_FIXED = 'fixed';
    public const TYPE_SPECIAL_PRICE = 'special_price';

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')
                  ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')
                  ->orWhere('ends_at', '>=', now());
            });
    }

    public function scopeForStore($query, int $storeId)
    {
        return $query->where(function ($q) use ($storeId) {
            $q->whereNull('store_id')
              ->orWhere('store_id', $storeId);
        });
    }

    public function scopeCurrentlyActive($query)
    {
        $now = now();
        $currentTime = $now->format('H:i:s');
        $dayOfWeek = $now->dayOfWeek ?: 7; // 1=Monday, 7=Sunday

        return $query->active()
            ->where('start_time', '<=', $currentTime)
            ->where('end_time', '>=', $currentTime)
            ->where(function ($q) use ($dayOfWeek) {
                $q->whereNull('days_of_week')
                  ->orWhereJsonContains('days_of_week', $dayOfWeek);
            });
    }

    /**
     * Check if this pricing is currently active (time + day)
     */
    public function isActiveNow(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = Carbon::now();

        // Check date range
        if ($this->starts_at && $now->lt($this->starts_at)) {
            return false;
        }
        if ($this->ends_at && $now->gt($this->ends_at)) {
            return false;
        }

        // Check day of week
        $dayOfWeek = $now->dayOfWeek ?: 7;
        if (!empty($this->days_of_week) && !in_array($dayOfWeek, $this->days_of_week)) {
            return false;
        }

        // Check time range
        $currentTime = $now->format('H:i:s');
        if ($this->start_time && $currentTime < $this->start_time) {
            return false;
        }
        if ($this->end_time && $currentTime > $this->end_time) {
            return false;
        }

        return true;
    }

    /**
     * Check if this pricing applies to a specific product
     */
    public function appliesToProduct(?int $productId, ?int $categoryId = null, ?int $brandId = null): bool
    {
        if ($this->applies_to === self::APPLIES_TO_ALL) {
            return true;
        }

        if ($this->applies_to === self::APPLIES_TO_PRODUCTS && $productId) {
            return in_array($productId, $this->product_ids ?? []);
        }

        if ($this->applies_to === self::APPLIES_TO_CATEGORIES && $categoryId) {
            return in_array($categoryId, $this->category_ids ?? []);
        }

        if ($this->applies_to === self::APPLIES_TO_BRANDS && $brandId) {
            return in_array($brandId, $this->brand_ids ?? []);
        }

        return false;
    }

    /**
     * Calculate the discounted price for a product
     */
    public function calculatePrice(float $originalPrice): float
    {
        if ($this->discount_type === self::TYPE_PERCENTAGE) {
            return $originalPrice * (1 - ($this->discount_value / 100));
        }

        if ($this->discount_type === self::TYPE_FIXED) {
            return max(0, $originalPrice - $this->discount_value);
        }

        if ($this->discount_type === self::TYPE_SPECIAL_PRICE) {
            return $this->discount_value;
        }

        return $originalPrice;
    }

    /**
     * Get the discount amount for a given price
     */
    public function getDiscountAmount(float $originalPrice): float
    {
        return $originalPrice - $this->calculatePrice($originalPrice);
    }

    /**
     * Get human-readable schedule description
     */
    public function getScheduleDescription(): string
    {
        $parts = [];

        if (!empty($this->days_of_week)) {
            $dayNames = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            $selectedDays = array_map(fn($d) => $dayNames[$d - 1] ?? '', $this->days_of_week);
            $parts[] = implode(', ', $selectedDays);
        } else {
            $parts[] = 'Every day';
        }

        if ($this->start_time && $this->end_time) {
            $parts[] = substr($this->start_time, 0, 5) . ' - ' . substr($this->end_time, 0, 5);
        }

        return implode(' | ', $parts);
    }

    /**
     * Get discount description
     */
    public function getDiscountDescription(): string
    {
        if ($this->discount_type === self::TYPE_PERCENTAGE) {
            return "{$this->discount_value}% off";
        }

        if ($this->discount_type === self::TYPE_FIXED) {
            return number_format($this->discount_value, 2) . ' off';
        }

        return 'Special price: ' . number_format($this->discount_value, 2);
    }
}
