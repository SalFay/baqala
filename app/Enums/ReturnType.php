<?php

namespace App\Enums;

enum ReturnType: string
{
    case REFUND = 'refund';
    case EXCHANGE = 'exchange';
    case STORE_CREDIT = 'store_credit';

    public function label(): string
    {
        return match($this) {
            self::REFUND => 'Refund',
            self::EXCHANGE => 'Exchange',
            self::STORE_CREDIT => 'Store Credit',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
