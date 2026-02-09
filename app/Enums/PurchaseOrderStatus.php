<?php

namespace App\Enums;

enum PurchaseOrderStatus: string
{
    case DRAFT = 'draft';
    case PENDING_APPROVAL = 'pending_approval';
    case APPROVED = 'approved';
    case ORDERED = 'ordered';
    case PARTIAL = 'partial';
    case RECEIVED = 'received';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Draft',
            self::PENDING_APPROVAL => 'Pending Approval',
            self::APPROVED => 'Approved',
            self::ORDERED => 'Ordered',
            self::PARTIAL => 'Partially Received',
            self::RECEIVED => 'Received',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::DRAFT => 'default',
            self::PENDING_APPROVAL => 'warning',
            self::APPROVED => 'processing',
            self::ORDERED => 'processing',
            self::PARTIAL => 'orange',
            self::RECEIVED => 'success',
            self::CANCELLED => 'error',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
