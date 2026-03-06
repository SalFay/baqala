<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Modifier extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'modifier_set_id',
        'name',
        'price_adjustment',
        'price_type',
        'is_default',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price_adjustment' => 'decimal:2',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function modifierSet(): BelongsTo
    {
        return $this->belongsTo(ModifierSet::class);
    }

    // Scopes
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

    // Helpers
    public function isFixedPrice(): bool
    {
        return $this->price_type === 'fixed';
    }

    public function isPercentagePrice(): bool
    {
        return $this->price_type === 'percentage';
    }

    /**
     * Calculate the actual price adjustment based on base price.
     */
    public function calculatePriceAdjustment(float $basePrice = 0): float
    {
        if ($this->isPercentagePrice()) {
            return round($basePrice * ($this->price_adjustment / 100), 2);
        }

        return (float) $this->price_adjustment;
    }

    /**
     * Get formatted price display.
     */
    public function getFormattedPrice(): string
    {
        if ($this->price_adjustment == 0) {
            return 'Free';
        }

        $prefix = $this->price_adjustment > 0 ? '+' : '';

        if ($this->isPercentagePrice()) {
            return $prefix . $this->price_adjustment . '%';
        }

        return $prefix . number_format($this->price_adjustment, 2);
    }
}
