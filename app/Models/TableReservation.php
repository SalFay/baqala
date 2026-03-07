<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TableReservation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_id',
        'table_id',
        'customer_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'reservation_date',
        'start_time',
        'end_time',
        'party_size',
        'status',
        'special_requests',
        'notes',
        'created_by',
        'confirmed_by',
        'confirmed_at',
    ];

    protected $casts = [
        'reservation_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'party_size' => 'integer',
        'confirmed_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETED = 'completed';
    const STATUS_NO_SHOW = 'no_show';

    // Relationships

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'table_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    // Scopes

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('reservation_date', '>=', today())
            ->whereIn('status', [self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('reservation_date', $date);
    }

    public function scopeForTable($query, int $tableId)
    {
        return $query->where('table_id', $tableId);
    }

    // Methods

    public function confirm(): bool
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_CONFIRMED,
            'confirmed_by' => auth()->id(),
            'confirmed_at' => now(),
        ]);

        return true;
    }

    public function cancel(): bool
    {
        if (in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED])) {
            return false;
        }

        $this->update(['status' => self::STATUS_CANCELLED]);
        return true;
    }

    public function complete(): bool
    {
        if ($this->status !== self::STATUS_CONFIRMED) {
            return false;
        }

        $this->update(['status' => self::STATUS_COMPLETED]);
        return true;
    }

    public function markNoShow(): bool
    {
        if ($this->status !== self::STATUS_CONFIRMED) {
            return false;
        }

        $this->update(['status' => self::STATUS_NO_SHOW]);
        return true;
    }

    /**
     * Get guest name (from customer or manual entry)
     */
    public function getGuestNameAttribute(): string
    {
        if ($this->customer) {
            return $this->customer->full_name;
        }
        return $this->customer_name ?? 'Guest';
    }

    /**
     * Get status color for UI
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'orange',
            self::STATUS_CONFIRMED => 'green',
            self::STATUS_CANCELLED => 'default',
            self::STATUS_COMPLETED => 'blue',
            self::STATUS_NO_SHOW => 'red',
            default => 'default',
        };
    }

    /**
     * Check if reservation conflicts with another
     */
    public static function hasConflict(int $tableId, $date, $startTime, $endTime = null, ?int $excludeId = null): bool
    {
        $query = static::where('table_id', $tableId)
            ->whereDate('reservation_date', $date)
            ->whereIn('status', [self::STATUS_PENDING, self::STATUS_CONFIRMED]);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        // Simple overlap check
        return $query->where(function ($q) use ($startTime, $endTime) {
            $q->where('start_time', '<=', $startTime)
                ->where(function ($q2) use ($startTime) {
                    $q2->whereNull('end_time')
                        ->orWhere('end_time', '>', $startTime);
                });
        })->exists();
    }
}
