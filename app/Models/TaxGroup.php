<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TaxGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_id',
        'name',
        'name_ar',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function taxRates(): BelongsToMany
    {
        return $this->belongsToMany(TaxRate::class, 'tax_group_rates')
            ->withPivot('sort_order')
            ->orderBy('pivot_sort_order')
            ->withTimestamps();
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Methods

    /**
     * Calculate total tax for this group (handles compound taxes)
     */
    public function calculateTax(float $amount): array
    {
        $breakdown = [];
        $runningAmount = $amount;

        $rates = $this->taxRates()->active()->orderBy('pivot_sort_order')->get();

        foreach ($rates as $rate) {
            $taxAmount = $rate->calculateTax($runningAmount);
            $breakdown[] = [
                'tax_rate_id' => $rate->id,
                'name' => $rate->name,
                'rate' => $rate->rate,
                'amount' => $taxAmount,
                'is_compound' => $rate->is_compound,
            ];

            // For compound taxes, add to running amount
            if ($rate->is_compound) {
                $runningAmount += $taxAmount;
            }
        }

        return [
            'breakdown' => $breakdown,
            'total_tax' => collect($breakdown)->sum('amount'),
        ];
    }

    /**
     * Get total combined rate (simple sum, doesn't account for compounding)
     */
    public function getTotalRateAttribute(): float
    {
        return $this->taxRates()->active()->sum('rate');
    }
}
