<?php

namespace App\Enums;

enum LoyaltyTransactionType: string
{
    case EARN = 'earn';
    case REDEEM = 'redeem';
    case EXPIRE = 'expire';
    case ADJUST = 'adjust';
    case BONUS = 'bonus';
    case REFUND = 'refund';

    public function label(): string
    {
        return match($this) {
            self::EARN => 'Points Earned',
            self::REDEEM => 'Points Redeemed',
            self::EXPIRE => 'Points Expired',
            self::ADJUST => 'Points Adjustment',
            self::BONUS => 'Bonus Points',
            self::REFUND => 'Points Refunded',
        };
    }

    public function isAddition(): bool
    {
        return in_array($this, [self::EARN, self::BONUS, self::REFUND]);
    }

    public function isDeduction(): bool
    {
        return in_array($this, [self::REDEEM, self::EXPIRE, self::ADJUST]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
