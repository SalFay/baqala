<?php

namespace App\Enums;

enum StockTransferStatus: string
{
    case DRAFT = 'draft';
    case PENDING = 'pending';
    case IN_TRANSIT = 'in_transit';
    case RECEIVED = 'received';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Draft',
            self::PENDING => 'Pending',
            self::IN_TRANSIT => 'In Transit',
            self::RECEIVED => 'Received',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::DRAFT => 'default',
            self::PENDING => 'warning',
            self::IN_TRANSIT => 'processing',
            self::RECEIVED => 'success',
            self::CANCELLED => 'error',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
