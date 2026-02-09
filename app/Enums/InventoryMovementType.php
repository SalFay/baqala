<?php

namespace App\Enums;

enum InventoryMovementType: string
{
    case PURCHASE = 'purchase';
    case SALE = 'sale';
    case RETURN_FROM_CUSTOMER = 'return_from_customer';
    case RETURN_TO_VENDOR = 'return_to_vendor';
    case TRANSFER_IN = 'transfer_in';
    case TRANSFER_OUT = 'transfer_out';
    case ADJUSTMENT_ADD = 'adjustment_add';
    case ADJUSTMENT_REMOVE = 'adjustment_remove';
    case DAMAGE = 'damage';
    case COUNT = 'count';
    case INITIAL = 'initial';

    public function label(): string
    {
        return match($this) {
            self::PURCHASE => 'Purchase',
            self::SALE => 'Sale',
            self::RETURN_FROM_CUSTOMER => 'Return from Customer',
            self::RETURN_TO_VENDOR => 'Return to Vendor',
            self::TRANSFER_IN => 'Transfer In',
            self::TRANSFER_OUT => 'Transfer Out',
            self::ADJUSTMENT_ADD => 'Stock Adjustment (Add)',
            self::ADJUSTMENT_REMOVE => 'Stock Adjustment (Remove)',
            self::DAMAGE => 'Damaged',
            self::COUNT => 'Stock Count',
            self::INITIAL => 'Initial Stock',
        };
    }

    public function isAddition(): bool
    {
        return in_array($this, [
            self::PURCHASE,
            self::RETURN_FROM_CUSTOMER,
            self::TRANSFER_IN,
            self::ADJUSTMENT_ADD,
            self::INITIAL,
        ]);
    }

    public function isDeduction(): bool
    {
        return in_array($this, [
            self::SALE,
            self::RETURN_TO_VENDOR,
            self::TRANSFER_OUT,
            self::ADJUSTMENT_REMOVE,
            self::DAMAGE,
        ]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
