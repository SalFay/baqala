<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends BaseModel
{
    const TYPE_CASH = 'cash';
    const TYPE_CARD = 'card';
    const TYPE_CREDIT = 'credit';
    const TYPE_CHEQUE = 'cheque';
    const TYPE_BANK_TRANSFER = 'bank_transfer';
    const TYPE_ONLINE = 'online';
    const TYPE_OTHER = 'other';

    protected $fillable = [
        'store_id',
        'name',
        'code',
        'description',
        'requires_reference',
        'reference_label',
        'settings',
        'icon',
        'is_system',
        'is_active',
        'allow_partial',
        'min_amount',
        'max_amount',
        'sort_order',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'requires_reference' => 'boolean',
        'allow_partial' => 'boolean',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    // ==================== Relationships ====================

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function cashRegisterTransactions(): HasMany
    {
        return $this->hasMany(CashRegisterTransaction::class);
    }

    // ==================== Scopes ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->active()->orderBy('sort_order');
    }

    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }

    // ==================== Helper Methods ====================

    public function isCash(): bool
    {
        return $this->code === self::TYPE_CASH;
    }

    public function isCredit(): bool
    {
        return $this->code === self::TYPE_CREDIT;
    }

    public function isCheque(): bool
    {
        return $this->code === self::TYPE_CHEQUE;
    }

    public function isOnline(): bool
    {
        return $this->code === self::TYPE_ONLINE;
    }

    /**
     * Check if an amount is valid for this payment method
     */
    public function isAmountValid(float $amount): bool
    {
        if ($this->min_amount && $amount < $this->min_amount) {
            return false;
        }

        if ($this->max_amount && $amount > $this->max_amount) {
            return false;
        }

        return true;
    }

    /**
     * Get setting value
     */
    public function getSetting(string $key, $default = null)
    {
        return data_get($this->settings, $key, $default);
    }
}
