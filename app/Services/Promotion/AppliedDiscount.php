<?php

namespace App\Services\Promotion;

/**
 * AppliedDiscount - Immutable record of a discount that was applied
 */
class AppliedDiscount
{
    public const TYPE_BULK = 'bulk';
    public const TYPE_RULE = 'rule';
    public const TYPE_COUPON = 'coupon';
    public const TYPE_LOYALTY_TIER = 'loyalty_tier';
    public const TYPE_LOYALTY_REDEMPTION = 'loyalty_redemption';
    public const TYPE_CUSTOMER_GROUP = 'customer_group';
    public const TYPE_TIME_BASED = 'time_based';
    public const TYPE_BOGO = 'bogo';
    public const TYPE_BUNDLE = 'bundle';
    public const TYPE_MANUAL = 'manual';

    public readonly string $type;
    public readonly ?int $sourceId;
    public readonly string $sourceName;
    public readonly string $discountType; // 'percentage' | 'fixed' | 'free_item'
    public readonly float $discountValue;
    public readonly float $discountAmount;
    public readonly float $originalAmount;
    public readonly int $priority;
    public readonly bool $isStackable;
    public readonly ?int $appliesToItemId;
    public readonly array $conditionsMet;
    public readonly string $description;

    public function __construct(
        string $type,
        ?int $sourceId,
        string $sourceName,
        string $discountType,
        float $discountValue,
        float $discountAmount,
        float $originalAmount,
        int $priority = 0,
        bool $isStackable = true,
        ?int $appliesToItemId = null,
        array $conditionsMet = [],
        string $description = ''
    ) {
        $this->type = $type;
        $this->sourceId = $sourceId;
        $this->sourceName = $sourceName;
        $this->discountType = $discountType;
        $this->discountValue = $discountValue;
        $this->discountAmount = $discountAmount;
        $this->originalAmount = $originalAmount;
        $this->priority = $priority;
        $this->isStackable = $isStackable;
        $this->appliesToItemId = $appliesToItemId;
        $this->conditionsMet = $conditionsMet;
        $this->description = $description ?: $this->generateDescription();
    }

    protected function generateDescription(): string
    {
        $desc = $this->sourceName;

        if ($this->discountType === 'percentage') {
            $desc .= " ({$this->discountValue}% off)";
        } elseif ($this->discountType === 'fixed') {
            $desc .= " (-" . number_format($this->discountValue, 2) . ")";
        } elseif ($this->discountType === 'free_item') {
            $desc .= " (Free item)";
        }

        return $desc;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'source_id' => $this->sourceId,
            'source_name' => $this->sourceName,
            'discount_type' => $this->discountType,
            'discount_value' => $this->discountValue,
            'discount_amount' => $this->discountAmount,
            'original_amount' => $this->originalAmount,
            'priority' => $this->priority,
            'is_stackable' => $this->isStackable,
            'applies_to_item_id' => $this->appliesToItemId,
            'conditions_met' => $this->conditionsMet,
            'description' => $this->description,
        ];
    }
}
