<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CustomerLedger extends BaseModel
{
    const TYPE_SALE = 'sale';
    const TYPE_PAYMENT = 'payment';
    const TYPE_REFUND = 'refund';
    const TYPE_ADJUSTMENT = 'adjustment';
    const TYPE_OPENING = 'opening';

    protected $table = 'customer_ledger';

    protected $fillable = [
        'store_id',
        'customer_id',
        'transaction_type',
        'reference_type',
        'reference_id',
        'debit',
        'credit',
        'balance_after',
        'description',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    // ==================== Relationships ====================

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
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

    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('transaction_type', $type);
    }

    public function scopeSales($query)
    {
        return $query->ofType(self::TYPE_SALE);
    }

    public function scopePayments($query)
    {
        return $query->ofType(self::TYPE_PAYMENT);
    }

    // ==================== Helper Methods ====================

    /**
     * Record a sale transaction (debit - customer owes money)
     */
    public static function recordSale(Customer $customer, float $amount, $reference = null, ?string $description = null): self
    {
        $currentBalance = $customer->current_balance;
        $newBalance = $currentBalance + $amount;

        $entry = static::create([
            'store_id' => $customer->store_id,
            'customer_id' => $customer->id,
            'transaction_type' => self::TYPE_SALE,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference?->id,
            'debit' => $amount,
            'credit' => 0,
            'balance_after' => $newBalance,
            'description' => $description ?? 'Sale',
            'created_by' => auth()->id(),
        ]);

        $customer->update(['current_balance' => $newBalance]);

        return $entry;
    }

    /**
     * Record a payment transaction (credit - customer paid)
     */
    public static function recordPayment(Customer $customer, float $amount, $reference = null, ?string $description = null): self
    {
        $currentBalance = $customer->current_balance;
        $newBalance = $currentBalance - $amount;

        $entry = static::create([
            'store_id' => $customer->store_id,
            'customer_id' => $customer->id,
            'transaction_type' => self::TYPE_PAYMENT,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference?->id,
            'debit' => 0,
            'credit' => $amount,
            'balance_after' => $newBalance,
            'description' => $description ?? 'Payment received',
            'created_by' => auth()->id(),
        ]);

        $customer->update(['current_balance' => $newBalance]);

        return $entry;
    }

    /**
     * Record a refund transaction
     */
    public static function recordRefund(Customer $customer, float $amount, $reference = null, ?string $description = null): self
    {
        $currentBalance = $customer->current_balance;
        $newBalance = $currentBalance - $amount;

        $entry = static::create([
            'store_id' => $customer->store_id,
            'customer_id' => $customer->id,
            'transaction_type' => self::TYPE_REFUND,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference?->id,
            'debit' => 0,
            'credit' => $amount,
            'balance_after' => $newBalance,
            'description' => $description ?? 'Refund',
            'created_by' => auth()->id(),
        ]);

        $customer->update(['current_balance' => $newBalance]);

        return $entry;
    }

    /**
     * Record an adjustment transaction
     */
    public static function recordAdjustment(Customer $customer, float $amount, string $type = 'debit', ?string $description = null): self
    {
        $currentBalance = $customer->current_balance;
        $debit = $type === 'debit' ? $amount : 0;
        $credit = $type === 'credit' ? $amount : 0;
        $newBalance = $type === 'debit' ? $currentBalance + $amount : $currentBalance - $amount;

        $entry = static::create([
            'store_id' => $customer->store_id,
            'customer_id' => $customer->id,
            'transaction_type' => self::TYPE_ADJUSTMENT,
            'debit' => $debit,
            'credit' => $credit,
            'balance_after' => $newBalance,
            'description' => $description ?? 'Balance adjustment',
            'created_by' => auth()->id(),
        ]);

        $customer->update(['current_balance' => $newBalance]);

        return $entry;
    }

    /**
     * Get net amount (debit - credit)
     */
    public function getNetAmountAttribute(): float
    {
        return $this->debit - $this->credit;
    }

    /**
     * Check if this is a debit transaction
     */
    public function isDebit(): bool
    {
        return $this->debit > 0;
    }

    /**
     * Check if this is a credit transaction
     */
    public function isCredit(): bool
    {
        return $this->credit > 0;
    }
}
