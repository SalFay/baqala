<?php

namespace App\Enums;

/**
 * Inventory status enum for tracking product availability
 */
enum InventoryStatus: string
{
    case AVAILABLE = 'Available';
    case SOLD = 'Sold';
    case VENDOR_RETURNED = 'Returned Vendor';
    case ORDER_RETURNED = 'Returned Order';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match ($this) {
            self::AVAILABLE => 'Available',
            self::SOLD => 'Sold',
            self::VENDOR_RETURNED => 'Returned to Vendor',
            self::ORDER_RETURNED => 'Returned from Order',
        };
    }

    /**
     * Get all possible values as array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get options for select dropdowns
     */
    public static function options(): array
    {
        return array_map(
            fn(self $status) => ['value' => $status->value, 'label' => $status->label()],
            self::cases()
        );
    }
}
