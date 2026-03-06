<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashRegister extends BaseModel
{
    const STATUS_CLOSED = 'closed';
    const STATUS_OPEN = 'open';

    protected $fillable = [
        'store_id',
        'location_id',
        'name',
        'user_id',
        'status',
        'opening_balance',
        'closing_balance',
        'opening_cash',
        'closing_cash',
        'expected_cash',
        'cash_difference',
        'opened_at',
        'closed_at',
        'opening_note',
        'closing_note',
        'denominations_opening',
        'denominations_closing',
        'opened_by',
        'closed_by',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'opening_cash' => 'decimal:2',
        'closing_cash' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'cash_difference' => 'decimal:2',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'denominations_opening' => 'array',
        'denominations_closing' => 'array',
    ];

    // ==================== Relationships ====================

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CashRegisterTransaction::class);
    }

    // ==================== Scopes ====================

    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    public function scopeClosed($query)
    {
        return $query->where('status', self::STATUS_CLOSED);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForLocation($query, int $locationId)
    {
        return $query->where('location_id', $locationId);
    }

    // ==================== Helper Methods ====================

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    /**
     * Open the cash register
     */
    public function open(float $openingCash, ?array $denominations = null, ?string $note = null): bool
    {
        if ($this->isOpen()) {
            return false;
        }

        return $this->update([
            'status' => self::STATUS_OPEN,
            'user_id' => auth()->id(),
            'opening_cash' => $openingCash,
            'opening_balance' => $openingCash,
            'denominations_opening' => $denominations,
            'opening_note' => $note,
            'opened_at' => now(),
            'opened_by' => auth()->id(),
            // Reset closing fields
            'closing_balance' => null,
            'closing_cash' => null,
            'expected_cash' => null,
            'cash_difference' => null,
            'closed_at' => null,
            'closing_note' => null,
            'denominations_closing' => null,
            'closed_by' => null,
        ]);
    }

    /**
     * Close the cash register
     */
    public function close(float $closingCash, ?array $denominations = null, ?string $note = null): bool
    {
        if ($this->isClosed()) {
            return false;
        }

        $expectedCash = $this->calculateExpectedCash();
        $difference = $closingCash - $expectedCash;

        return $this->update([
            'status' => self::STATUS_CLOSED,
            'closing_cash' => $closingCash,
            'closing_balance' => $this->calculateClosingBalance(),
            'expected_cash' => $expectedCash,
            'cash_difference' => $difference,
            'denominations_closing' => $denominations,
            'closing_note' => $note,
            'closed_at' => now(),
            'closed_by' => auth()->id(),
        ]);
    }

    /**
     * Calculate expected cash in register
     */
    public function calculateExpectedCash(): float
    {
        $cashMethodId = PaymentMethod::where('code', 'cash')->value('id');

        $cashIn = $this->transactions()
            ->whereIn('transaction_type', [
                CashRegisterTransaction::TYPE_SALE,
                CashRegisterTransaction::TYPE_PAY_IN,
            ])
            ->where(function ($q) use ($cashMethodId) {
                $q->where('payment_method_id', $cashMethodId)
                    ->orWhereNull('payment_method_id');
            })
            ->sum('amount');

        $cashOut = $this->transactions()
            ->whereIn('transaction_type', [
                CashRegisterTransaction::TYPE_REFUND,
                CashRegisterTransaction::TYPE_PAY_OUT,
            ])
            ->where(function ($q) use ($cashMethodId) {
                $q->where('payment_method_id', $cashMethodId)
                    ->orWhereNull('payment_method_id');
            })
            ->sum('amount');

        return $this->opening_cash + $cashIn - $cashOut;
    }

    /**
     * Calculate closing balance (all payment methods)
     */
    public function calculateClosingBalance(): float
    {
        $totalIn = $this->transactions()
            ->whereIn('transaction_type', [
                CashRegisterTransaction::TYPE_SALE,
                CashRegisterTransaction::TYPE_PAY_IN,
            ])
            ->sum('amount');

        $totalOut = $this->transactions()
            ->whereIn('transaction_type', [
                CashRegisterTransaction::TYPE_REFUND,
                CashRegisterTransaction::TYPE_PAY_OUT,
            ])
            ->sum('amount');

        return $this->opening_balance + $totalIn - $totalOut;
    }

    /**
     * Record a sale transaction
     */
    public function recordSale(float $amount, ?int $paymentMethodId = null, $reference = null): CashRegisterTransaction
    {
        return $this->transactions()->create([
            'transaction_type' => CashRegisterTransaction::TYPE_SALE,
            'payment_method_id' => $paymentMethodId,
            'amount' => $amount,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference?->id,
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Record a refund transaction
     */
    public function recordRefund(float $amount, ?int $paymentMethodId = null, $reference = null): CashRegisterTransaction
    {
        return $this->transactions()->create([
            'transaction_type' => CashRegisterTransaction::TYPE_REFUND,
            'payment_method_id' => $paymentMethodId,
            'amount' => $amount,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference?->id,
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Record a pay-in (cash added to register)
     */
    public function payIn(float $amount, ?string $note = null): CashRegisterTransaction
    {
        return $this->transactions()->create([
            'transaction_type' => CashRegisterTransaction::TYPE_PAY_IN,
            'amount' => $amount,
            'note' => $note,
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Record a pay-out (cash removed from register)
     */
    public function payOut(float $amount, ?string $note = null): CashRegisterTransaction
    {
        return $this->transactions()->create([
            'transaction_type' => CashRegisterTransaction::TYPE_PAY_OUT,
            'amount' => $amount,
            'note' => $note,
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Get transaction summary
     */
    public function getTransactionSummary(): array
    {
        $summary = [];

        $paymentMethods = PaymentMethod::active()->get();

        foreach ($paymentMethods as $method) {
            $sales = $this->transactions()
                ->where('transaction_type', CashRegisterTransaction::TYPE_SALE)
                ->where('payment_method_id', $method->id)
                ->sum('amount');

            $refunds = $this->transactions()
                ->where('transaction_type', CashRegisterTransaction::TYPE_REFUND)
                ->where('payment_method_id', $method->id)
                ->sum('amount');

            if ($sales > 0 || $refunds > 0) {
                $summary[$method->name] = [
                    'sales' => $sales,
                    'refunds' => $refunds,
                    'net' => $sales - $refunds,
                ];
            }
        }

        $summary['pay_in'] = $this->transactions()
            ->where('transaction_type', CashRegisterTransaction::TYPE_PAY_IN)
            ->sum('amount');

        $summary['pay_out'] = $this->transactions()
            ->where('transaction_type', CashRegisterTransaction::TYPE_PAY_OUT)
            ->sum('amount');

        return $summary;
    }

    /**
     * Get the currently open register for a user
     */
    public static function getOpenRegisterForUser(int $userId): ?self
    {
        return static::open()->forUser($userId)->first();
    }

    /**
     * Get or create an open register for POS
     */
    public static function getOrCreateForPOS(?int $locationId = null): self
    {
        $userId = auth()->id();

        $register = static::open()->forUser($userId)->first();

        if (!$register) {
            $register = static::create([
                'store_id' => auth()->user()->store_id ?? 1,
                'location_id' => $locationId,
                'name' => 'Register ' . now()->format('Y-m-d'),
                'user_id' => $userId,
                'status' => self::STATUS_CLOSED,
            ]);
        }

        return $register;
    }
}
