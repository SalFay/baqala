<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cheque extends BaseModel
{
    use SoftDeletes;

    const STATUS_PENDING = 'pending';
    const STATUS_DEPOSITED = 'deposited';
    const STATUS_CLEARED = 'cleared';
    const STATUS_BOUNCED = 'bounced';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'store_id',
        'payment_id',
        'customer_id',
        'cheque_number',
        'bank_name',
        'bank_branch',
        'account_number',
        'amount',
        'cheque_date',
        'due_date',
        'status',
        'deposited_at',
        'cleared_at',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'cheque_date' => 'date',
        'due_date' => 'date',
        'deposited_at' => 'datetime',
        'cleared_at' => 'datetime',
    ];

    // ==================== Relationships ====================

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ==================== Scopes ====================

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeDeposited($query)
    {
        return $query->where('status', self::STATUS_DEPOSITED);
    }

    public function scopeCleared($query)
    {
        return $query->where('status', self::STATUS_CLEARED);
    }

    public function scopeBounced($query)
    {
        return $query->where('status', self::STATUS_BOUNCED);
    }

    public function scopeDueToday($query)
    {
        return $query->pending()->whereDate('due_date', today());
    }

    public function scopeOverdue($query)
    {
        return $query->pending()->whereDate('due_date', '<', today());
    }

    public function scopeDueSoon($query, int $days = 7)
    {
        return $query->pending()
            ->whereDate('due_date', '>=', today())
            ->whereDate('due_date', '<=', today()->addDays($days));
    }

    // ==================== Helper Methods ====================

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isDeposited(): bool
    {
        return $this->status === self::STATUS_DEPOSITED;
    }

    public function isCleared(): bool
    {
        return $this->status === self::STATUS_CLEARED;
    }

    public function isBounced(): bool
    {
        return $this->status === self::STATUS_BOUNCED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isOverdue(): bool
    {
        return $this->isPending() && $this->due_date && $this->due_date->isPast();
    }

    /**
     * Mark cheque as deposited
     */
    public function deposit(): bool
    {
        if (!$this->isPending()) {
            return false;
        }

        return $this->update([
            'status' => self::STATUS_DEPOSITED,
            'deposited_at' => now(),
        ]);
    }

    /**
     * Mark cheque as cleared
     */
    public function clear(): bool
    {
        if (!$this->isDeposited()) {
            return false;
        }

        return $this->update([
            'status' => self::STATUS_CLEARED,
            'cleared_at' => now(),
        ]);
    }

    /**
     * Mark cheque as bounced
     */
    public function bounce(?string $notes = null): bool
    {
        if ($this->isCleared() || $this->isCancelled()) {
            return false;
        }

        return $this->update([
            'status' => self::STATUS_BOUNCED,
            'notes' => $notes ?? $this->notes,
        ]);
    }

    /**
     * Cancel the cheque
     */
    public function cancel(?string $notes = null): bool
    {
        if ($this->isCleared() || $this->isBounced()) {
            return false;
        }

        return $this->update([
            'status' => self::STATUS_CANCELLED,
            'notes' => $notes ?? $this->notes,
        ]);
    }

    /**
     * Get days until due
     */
    public function getDaysUntilDueAttribute(): ?int
    {
        if (!$this->due_date) {
            return null;
        }

        return today()->diffInDays($this->due_date, false);
    }

    /**
     * Get status color for display
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_DEPOSITED => 'processing',
            self::STATUS_CLEARED => 'success',
            self::STATUS_BOUNCED => 'error',
            self::STATUS_CANCELLED => 'default',
            default => 'default',
        };
    }
}
