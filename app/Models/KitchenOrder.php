<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KitchenOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'order_id',
        'order_item_id',
        'status',
        'station',
        'priority',
        'notes',
        'started_at',
        'completed_at',
        'prepared_by',
        'estimated_time',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'estimated_time' => 'integer',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_PREPARING = 'preparing';
    const STATUS_READY = 'ready';
    const STATUS_SERVED = 'served';
    const STATUS_CANCELLED = 'cancelled';

    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_RUSH = 'rush';
    const PRIORITY_VIP = 'vip';

    // Relationships

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    // Scopes

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopePreparing($query)
    {
        return $query->where('status', self::STATUS_PREPARING);
    }

    public function scopeReady($query)
    {
        return $query->where('status', self::STATUS_READY);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_PREPARING]);
    }

    public function scopeForStation($query, string $station)
    {
        return $query->where('station', $station);
    }

    public function scopePrioritized($query)
    {
        return $query->orderByRaw("CASE
            WHEN priority = 'vip' THEN 1
            WHEN priority = 'rush' THEN 2
            ELSE 3
        END")
        ->orderBy('created_at');
    }

    // Methods

    public function startPreparing(): bool
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_PREPARING,
            'started_at' => now(),
            'prepared_by' => auth()->id(),
        ]);

        return true;
    }

    public function markReady(): bool
    {
        if (!in_array($this->status, [self::STATUS_PENDING, self::STATUS_PREPARING])) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_READY,
            'completed_at' => now(),
        ]);

        return true;
    }

    public function markServed(): bool
    {
        if ($this->status !== self::STATUS_READY) {
            return false;
        }

        $this->update(['status' => self::STATUS_SERVED]);
        return true;
    }

    public function cancel(): bool
    {
        if (in_array($this->status, [self::STATUS_SERVED, self::STATUS_CANCELLED])) {
            return false;
        }

        $this->update(['status' => self::STATUS_CANCELLED]);
        return true;
    }

    /**
     * Get preparation time in minutes
     */
    public function getPreparationTimeAttribute(): ?int
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }
        return $this->started_at->diffInMinutes($this->completed_at);
    }

    /**
     * Get waiting time in minutes
     */
    public function getWaitingTimeAttribute(): int
    {
        $endTime = $this->started_at ?? now();
        return $this->created_at->diffInMinutes($endTime);
    }

    /**
     * Get status color for UI
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'orange',
            self::STATUS_PREPARING => 'blue',
            self::STATUS_READY => 'green',
            self::STATUS_SERVED => 'default',
            self::STATUS_CANCELLED => 'red',
            default => 'default',
        };
    }

    /**
     * Get priority color for UI
     */
    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            self::PRIORITY_VIP => 'gold',
            self::PRIORITY_RUSH => 'red',
            default => 'default',
        };
    }
}
