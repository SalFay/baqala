<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'name',
        'short_name',
        'is_base_unit',
        'base_unit_id',
        'conversion_rate',
        'allow_decimal',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_base_unit' => 'boolean',
        'conversion_rate' => 'decimal:4',
        'allow_decimal' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // ==================== Relationships ====================

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function baseUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    public function derivedUnits(): HasMany
    {
        return $this->hasMany(Unit::class, 'base_unit_id');
    }

    public function productUnits(): HasMany
    {
        return $this->hasMany(ProductUnit::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_units')
            ->withPivot(['is_purchase_unit', 'is_sale_unit', 'is_default', 'multiplier', 'price_per_unit'])
            ->withTimestamps();
    }

    // ==================== Scopes ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeBaseUnits($query)
    {
        return $query->where('is_base_unit', true);
    }

    public function scopeDerivedUnits($query)
    {
        return $query->where('is_base_unit', false);
    }

    // ==================== Helper Methods ====================

    /**
     * Convert a quantity from this unit to the base unit
     */
    public function convertToBaseUnit(float $quantity): float
    {
        if ($this->is_base_unit) {
            return $quantity;
        }

        return $quantity * $this->conversion_rate;
    }

    /**
     * Convert a quantity from the base unit to this unit
     */
    public function convertFromBaseUnit(float $quantity): float
    {
        if ($this->is_base_unit) {
            return $quantity;
        }

        if ($this->conversion_rate == 0) {
            return 0;
        }

        return $quantity / $this->conversion_rate;
    }

    /**
     * Convert a quantity from this unit to another unit
     */
    public function convertTo(float $quantity, Unit $targetUnit): float
    {
        // If same unit, no conversion needed
        if ($this->id === $targetUnit->id) {
            return $quantity;
        }

        // First convert to base unit
        $baseQuantity = $this->convertToBaseUnit($quantity);

        // Then convert from base to target
        return $targetUnit->convertFromBaseUnit($baseQuantity);
    }

    /**
     * Format quantity according to allow_decimal setting
     */
    public function formatQuantity(float $quantity): string
    {
        if ($this->allow_decimal) {
            return number_format($quantity, 2);
        }

        return number_format(round($quantity), 0);
    }

    /**
     * Get display name with short name
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->name} ({$this->short_name})";
    }

    /**
     * Check if this is a derived unit (not a base unit)
     */
    public function isDerivedUnit(): bool
    {
        return !$this->is_base_unit;
    }

    /**
     * Get the ultimate base unit in the chain
     */
    public function getUltimateBaseUnit(): Unit
    {
        if ($this->is_base_unit) {
            return $this;
        }

        return $this->baseUnit ? $this->baseUnit->getUltimateBaseUnit() : $this;
    }
}
