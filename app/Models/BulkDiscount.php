<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BulkDiscount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_id',
        'name',
        'description',
        'product_id',
        'category_id',
        'brand_id',
        'min_quantity',
        'max_quantity',
        'discount_type',
        'discount_amount',
        'selling_price_group_id',
        'customer_group_id',
        'priority',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'min_quantity' => 'decimal:3',
        'max_quantity' => 'decimal:3',
        'discount_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'starts_at' => 'date',
        'ends_at' => 'date',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function sellingPriceGroup(): BelongsTo
    {
        return $this->belongsTo(SellingPriceGroup::class);
    }

    public function customerGroup(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class);
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

    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeForCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderByDesc('priority')->orderBy('min_quantity');
    }

    /**
     * Calculate discount for a given quantity.
     */
    public function calculateDiscount(float $unitPrice, float $quantity): float
    {
        if ($quantity < $this->min_quantity) {
            return 0;
        }

        if ($this->max_quantity && $quantity > $this->max_quantity) {
            return 0;
        }

        if ($this->discount_type === 'percentage') {
            return ($unitPrice * $quantity) * ($this->discount_amount / 100);
        }

        return $this->discount_amount * $quantity;
    }

    /**
     * Check if discount is applicable for given quantity.
     */
    public function isApplicable(float $quantity): bool
    {
        if ($quantity < $this->min_quantity) {
            return false;
        }

        if ($this->max_quantity && $quantity > $this->max_quantity) {
            return false;
        }

        return true;
    }
}
