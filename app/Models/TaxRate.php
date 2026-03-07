<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TaxRate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_id',
        'name',
        'name_ar',
        'rate',
        'is_default',
        'is_compound',
        'is_recoverable',
        'tax_number',
        'is_active',
        'description',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'is_default' => 'boolean',
        'is_compound' => 'boolean',
        'is_recoverable' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Relationships

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function taxGroups(): BelongsToMany
    {
        return $this->belongsToMany(TaxGroup::class, 'tax_group_rates')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeCompound($query)
    {
        return $query->where('is_compound', true);
    }

    public function scopeNonCompound($query)
    {
        return $query->where('is_compound', false);
    }

    // Methods

    /**
     * Calculate tax amount for a given value
     */
    public function calculateTax(float $amount): float
    {
        return round($amount * ($this->rate / 100), 2);
    }

    /**
     * Calculate amount including tax
     */
    public function calculateAmountWithTax(float $amount): float
    {
        return round($amount + $this->calculateTax($amount), 2);
    }

    /**
     * Get the default tax rate
     */
    public static function getDefault(): ?self
    {
        return static::active()->default()->first();
    }
}
