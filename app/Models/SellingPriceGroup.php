<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SellingPriceGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_id',
        'name',
        'description',
        'price_calculation_type',
        'price_calculation_amount',
        'is_default',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price_calculation_amount' => 'decimal:2',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function customerGroups(): HasMany
    {
        return $this->hasMany(CustomerGroup::class);
    }

    public function productPrices(): HasMany
    {
        return $this->hasMany(ProductPriceGroupPrice::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Calculate price for a product based on this price group.
     */
    public function calculatePrice(float $basePrice): float
    {
        if ($this->price_calculation_type === 'percentage') {
            return $basePrice * (1 + ($this->price_calculation_amount / 100));
        }
        return $basePrice + $this->price_calculation_amount;
    }
}
