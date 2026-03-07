<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductTaxSetting;
use App\Models\TaxGroup;
use App\Models\TaxRate;
use Illuminate\Support\Collection;

class TaxService
{
    /**
     * Calculate tax for a product
     *
     * @param Product $product
     * @param float $quantity
     * @param float $priceAfterDiscount The price per unit after any discounts
     * @return array
     */
    public function calculateTax(Product $product, float $quantity, float $priceAfterDiscount): array
    {
        $lineTotal = $quantity * $priceAfterDiscount;

        // Check if product has custom tax settings
        $taxSetting = ProductTaxSetting::where('product_id', $product->id)->first();

        if ($taxSetting) {
            $result = $taxSetting->calculateTax($lineTotal);
        } else {
            // Use default tax rate
            $result = $this->calculateWithDefaultRate($lineTotal);
        }

        return [
            'line_total' => $lineTotal,
            'tax_amount' => $result['total_tax'],
            'total_with_tax' => $lineTotal + $result['total_tax'],
            'breakdown' => $result['breakdown'],
        ];
    }

    /**
     * Get applicable tax rates for a product
     */
    public function getApplicableTaxRates(Product $product): Collection
    {
        $taxSetting = ProductTaxSetting::where('product_id', $product->id)->first();

        if ($taxSetting) {
            if ($taxSetting->is_tax_exempt) {
                return collect();
            }

            if ($taxSetting->taxGroup) {
                return $taxSetting->taxGroup->taxRates()->active()->get();
            }

            if ($taxSetting->taxRate) {
                return collect([$taxSetting->taxRate]);
            }
        }

        // Return default tax rate
        $defaultRate = TaxRate::getDefault();
        return $defaultRate ? collect([$defaultRate]) : collect();
    }

    /**
     * Calculate compound taxes
     * Compound tax is calculated on amount + previous taxes
     */
    public function calculateCompoundTax(float $amount, Collection $taxRates): array
    {
        $breakdown = [];
        $runningAmount = $amount;

        // Sort: non-compound first, then compound
        $sortedRates = $taxRates->sortBy('is_compound');

        foreach ($sortedRates as $rate) {
            $taxAmount = round($runningAmount * ($rate->rate / 100), 2);
            $breakdown[] = [
                'tax_rate_id' => $rate->id,
                'name' => $rate->name,
                'rate' => $rate->rate,
                'amount' => $taxAmount,
                'is_compound' => $rate->is_compound,
            ];

            // For compound taxes, add to running amount for next calculation
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
     * Format tax breakdown for invoice display
     */
    public function formatTaxBreakdown(array $taxAmounts): array
    {
        $grouped = [];

        foreach ($taxAmounts as $tax) {
            $key = $tax['tax_rate_id'] ?? $tax['name'];
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'name' => $tax['name'],
                    'rate' => $tax['rate'],
                    'amount' => 0,
                ];
            }
            $grouped[$key]['amount'] += $tax['amount'];
        }

        return array_values($grouped);
    }

    /**
     * Calculate tax using default rate
     */
    protected function calculateWithDefaultRate(float $amount): array
    {
        $defaultRate = TaxRate::getDefault();

        if (!$defaultRate) {
            return [
                'breakdown' => [],
                'total_tax' => 0,
            ];
        }

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

    /**
     * Calculate taxes for an order
     * Aggregates tax breakdown from all items
     */
    public function calculateOrderTaxes(array $items): array
    {
        $totalTax = 0;
        $breakdown = [];

        foreach ($items as $item) {
            $totalTax += $item['tax_amount'] ?? 0;

            if (!empty($item['tax_breakdown'])) {
                foreach ($item['tax_breakdown'] as $tax) {
                    $key = $tax['tax_rate_id'];
                    if (!isset($breakdown[$key])) {
                        $breakdown[$key] = [
                            'tax_rate_id' => $tax['tax_rate_id'],
                            'name' => $tax['name'],
                            'rate' => $tax['rate'],
                            'amount' => 0,
                        ];
                    }
                    $breakdown[$key]['amount'] += $tax['amount'];
                }
            }
        }

        return [
            'total_tax' => round($totalTax, 2),
            'breakdown' => array_values($breakdown),
        ];
    }

    /**
     * Get all active tax rates
     */
    public function getActiveTaxRates(): Collection
    {
        return TaxRate::active()->orderBy('name')->get();
    }

    /**
     * Get all active tax groups
     */
    public function getActiveTaxGroups(): Collection
    {
        return TaxGroup::active()->with('taxRates')->orderBy('name')->get();
    }

    /**
     * Set product tax settings
     */
    public function setProductTaxSetting(
        Product $product,
        ?int $taxRateId = null,
        ?int $taxGroupId = null,
        bool $isTaxExempt = false
    ): ProductTaxSetting {
        return ProductTaxSetting::updateOrCreate(
            ['product_id' => $product->id],
            [
                'tax_rate_id' => $isTaxExempt ? null : $taxRateId,
                'tax_group_id' => $isTaxExempt ? null : $taxGroupId,
                'is_tax_exempt' => $isTaxExempt,
            ]
        );
    }
}
