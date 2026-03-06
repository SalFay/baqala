<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductUnit extends BaseModel
{
    protected $fillable = [
        'product_id',
        'unit_id',
        'is_purchase_unit',
        'is_sale_unit',
        'is_default',
        'multiplier',
        'price_per_unit',
    ];

    protected $casts = [
        'is_purchase_unit' => 'boolean',
        'is_sale_unit' => 'boolean',
        'is_default' => 'boolean',
        'multiplier' => 'decimal:4',
        'price_per_unit' => 'decimal:2',
    ];

    // ==================== Relationships ====================

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    // ==================== Scopes ====================

    public function scopeForSale($query)
    {
        return $query->where('is_sale_unit', true);
    }

    public function scopeForPurchase($query)
    {
        return $query->where('is_purchase_unit', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    // ==================== Helper Methods ====================

    /**
     * Convert a quantity in this product unit to base product quantity
     * e.g., 2 boxes with multiplier 12 = 24 pieces
     */
    public function convertToBaseQuantity(float $quantity): float
    {
        return $quantity * $this->multiplier;
    }

    /**
     * Convert a base quantity to this product unit quantity
     * e.g., 24 pieces with multiplier 12 = 2 boxes
     */
    public function convertFromBaseQuantity(float $baseQuantity): float
    {
        if ($this->multiplier == 0) {
            return 0;
        }

        return $baseQuantity / $this->multiplier;
    }

    /**
     * Get the effective price for this unit
     * Uses price_per_unit if set, otherwise calculates from product base price
     */
    public function getEffectivePrice(?float $basePrice = null): float
    {
        if ($this->price_per_unit !== null) {
            return (float) $this->price_per_unit;
        }

        // Calculate from base price using multiplier
        if ($basePrice !== null) {
            return $basePrice * $this->multiplier;
        }

        // Get from product if available
        if ($this->product) {
            return $this->product->sale_price * $this->multiplier;
        }

        return 0;
    }

    /**
     * Calculate total for a given quantity in this unit
     */
    public function calculateTotal(float $quantity, ?float $basePrice = null): float
    {
        return $quantity * $this->getEffectivePrice($basePrice);
    }

    /**
     * Get display name combining unit name with multiplier info
     */
    public function getDisplayNameAttribute(): string
    {
        $unitName = $this->unit ? $this->unit->name : 'Unknown';

        if ($this->multiplier > 1) {
            return "{$unitName} ({$this->multiplier}x)";
        }

        return $unitName;
    }

    /**
     * Get short display name
     */
    public function getShortNameAttribute(): string
    {
        return $this->unit ? $this->unit->short_name : '?';
    }
}
