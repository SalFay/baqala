<?php

namespace App\Enums;

enum AccountTransactionType: string
{
    case SALE = 'sale';
    case PURCHASE = 'purchase';
    case PAYMENT_RECEIVED = 'payment_received';
    case PAYMENT_MADE = 'payment_made';
    case REFUND = 'refund';
    case EXPENSE = 'expense';
    case ADJUSTMENT = 'adjustment';
    case TRANSFER = 'transfer';

    public function label(): string
    {
        return match($this) {
            self::SALE => 'Sale',
            self::PURCHASE => 'Purchase',
            self::PAYMENT_RECEIVED => 'Payment Received',
            self::PAYMENT_MADE => 'Payment Made',
            self::REFUND => 'Refund',
            self::EXPENSE => 'Expense',
            self::ADJUSTMENT => 'Adjustment',
            self::TRANSFER => 'Transfer',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
