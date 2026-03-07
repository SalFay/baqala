<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductTaxSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'tax_rate_id',
        'tax_group_id',
        'is_tax_exempt',
    ];

    protected $casts = [
        'is_tax_exempt' => 'boolean',
    ];

    // Relationships

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class);
    }

    public function taxGroup(): BelongsTo
    {
        return $this->belongsTo(TaxGroup::class);
    }

    // Methods

    /**
     * Calculate tax for this product setting
     */
    public function calculateTax(float $amount): array
    {
        if ($this->is_tax_exempt) {
            return [
                'breakdown' => [],
                'total_tax' => 0,
            ];
        }

        // Use tax group if available
        if ($this->taxGroup) {
            return $this->taxGroup->calculateTax($amount);
        }

        // Use single tax rate
        if ($this->taxRate) {
            $taxAmount = $this->taxRate->calculateTax($amount);
            return [
                'breakdown' => [
                    [
                        'tax_rate_id' => $this->taxRate->id,
                        'name' => $this->taxRate->name,
                        'rate' => $this->taxRate->rate,
                        'amount' => $taxAmount,
                        'is_compound' => false,
                    ],
                ],
                'total_tax' => $taxAmount,
            ];
        }

        // Fall back to default tax rate
        $defaultRate = TaxRate::getDefault();
        if ($defaultRate) {
            $taxAmount = $defaultRate->calculateTax($amount);
            return [
                'breakdown' => [
                    [
                        'tax_rate_id' => $defaultRate->id,
                        'name' => $defaultRate->name,
                        'rate' => $defaultRate->rate,
                        'amount' => $taxAmount,
                        'is_compound' => false,
                    ],
                ],
                'total_tax' => $taxAmount,
            ];
        }

        return [
            'breakdown' => [],
            'total_tax' => 0,
        ];
    }
}
