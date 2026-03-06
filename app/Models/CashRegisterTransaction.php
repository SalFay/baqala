<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CashRegisterTransaction extends BaseModel
{
    const TYPE_SALE = 'sale';
    const TYPE_REFUND = 'refund';
    const TYPE_PAY_IN = 'pay_in';
    const TYPE_PAY_OUT = 'pay_out';
    const TYPE_ADJUSTMENT = 'adjustment';

    protected $fillable = [
        'cash_register_id',
        'transaction_type',
        'payment_method_id',
        'amount',
        'reference_type',
        'reference_id',
        'note',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // ==================== Relationships ====================

    public function cashRegister(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ==================== Scopes ====================

    public function scopeSales($query)
    {
        return $query->where('transaction_type', self::TYPE_SALE);
    }

    public function scopeRefunds($query)
    {
        return $query->where('transaction_type', self::TYPE_REFUND);
    }

    public function scopePayIns($query)
    {
        return $query->where('transaction_type', self::TYPE_PAY_IN);
    }

    public function scopePayOuts($query)
    {
        return $query->where('transaction_type', self::TYPE_PAY_OUT);
    }

    public function scopeForPaymentMethod($query, int $paymentMethodId)
    {
        return $query->where('payment_method_id', $paymentMethodId);
    }

    public function scopeCashOnly($query)
    {
        $cashMethodId = PaymentMethod::where('code', 'cash')->value('id');
        return $query->where('payment_method_id', $cashMethodId);
    }

    // ==================== Helper Methods ====================

    public function isSale(): bool
    {
        return $this->transaction_type === self::TYPE_SALE;
    }

    public function isRefund(): bool
    {
        return $this->transaction_type === self::TYPE_REFUND;
    }

    public function isPayIn(): bool
    {
        return $this->transaction_type === self::TYPE_PAY_IN;
    }

    public function isPayOut(): bool
    {
        return $this->transaction_type === self::TYPE_PAY_OUT;
    }

    /**
     * Check if this is a credit (money in)
     */
    public function isCredit(): bool
    {
        return in_array($this->transaction_type, [self::TYPE_SALE, self::TYPE_PAY_IN]);
    }

    /**
     * Check if this is a debit (money out)
     */
    public function isDebit(): bool
    {
        return in_array($this->transaction_type, [self::TYPE_REFUND, self::TYPE_PAY_OUT]);
    }

    /**
     * Get signed amount (positive for credits, negative for debits)
     */
    public function getSignedAmountAttribute(): float
    {
        return $this->isCredit() ? $this->amount : -$this->amount;
    }

    /**
     * Get type label for display
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->transaction_type) {
            self::TYPE_SALE => 'Sale',
            self::TYPE_REFUND => 'Refund',
            self::TYPE_PAY_IN => 'Pay In',
            self::TYPE_PAY_OUT => 'Pay Out',
            self::TYPE_ADJUSTMENT => 'Adjustment',
            default => ucfirst($this->transaction_type),
        };
    }

    /**
     * Get type color for display
     */
    public function getTypeColorAttribute(): string
    {
        return match ($this->transaction_type) {
            self::TYPE_SALE => 'success',
            self::TYPE_REFUND => 'error',
            self::TYPE_PAY_IN => 'processing',
            self::TYPE_PAY_OUT => 'warning',
            self::TYPE_ADJUSTMENT => 'default',
            default => 'default',
        };
    }
}
