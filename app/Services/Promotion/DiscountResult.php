<?php

namespace App\Services\Promotion;

use Illuminate\Support\Collection;

/**
 * DiscountResult - Contains the complete result of discount calculations
 */
class DiscountResult
{
    /** @var Collection<AppliedDiscount> */
    public readonly Collection $appliedDiscounts;
    public readonly float $totalDiscount;
    public readonly float $originalTotal;
    public readonly float $finalTotal;
    public readonly bool $hasFreeShipping;
    public readonly array $freeItems;
    public readonly array $itemDiscounts;
    public readonly array $warnings;

    public function __construct(
        Collection $appliedDiscounts,
        float $originalTotal,
        bool $hasFreeShipping = false,
        array $freeItems = [],
        array $itemDiscounts = [],
        array $warnings = []
    ) {
        $this->appliedDiscounts = $appliedDiscounts;
        $this->totalDiscount = $appliedDiscounts->sum('discountAmount');
        $this->originalTotal = $originalTotal;
        $this->finalTotal = max(0, $originalTotal - $this->totalDiscount);
        $this->hasFreeShipping = $hasFreeShipping;
        $this->freeItems = $freeItems;
        $this->itemDiscounts = $itemDiscounts;
        $this->warnings = $warnings;
    }

    public function hasDiscounts(): bool
    {
        return $this->appliedDiscounts->isNotEmpty();
    }

    public function getDiscountsByType(string $type): Collection
    {
        return $this->appliedDiscounts->filter(fn($d) => $d->type === $type);
    }

    public function getDiscountsForItem(int $itemId): Collection
    {
        return $this->appliedDiscounts->filter(fn($d) => $d->appliesToItemId === $itemId);
    }

    public function getDiscountBreakdown(): array
    {
        return $this->appliedDiscounts
            ->groupBy('type')
            ->map(fn($discounts) => [
                'count' => $discounts->count(),
                'total' => $discounts->sum('discountAmount'),
                'discounts' => $discounts->map->toArray()->values()->toArray(),
            ])
            ->toArray();
    }

    public function getSavingsPercentage(): float
    {
        if ($this->originalTotal <= 0) {
            return 0;
        }
        return round(($this->totalDiscount / $this->originalTotal) * 100, 2);
    }

    public function toArray(): array
    {
        return [
            'original_total' => $this->originalTotal,
            'total_discount' => $this->totalDiscount,
            'final_total' => $this->finalTotal,
            'savings_percentage' => $this->getSavingsPercentage(),
            'has_free_shipping' => $this->hasFreeShipping,
            'free_items' => $this->freeItems,
            'applied_discounts' => $this->appliedDiscounts->map->toArray()->values()->toArray(),
            'breakdown' => $this->getDiscountBreakdown(),
            'warnings' => $this->warnings,
        ];
    }
}
