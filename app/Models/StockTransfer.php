<?php

namespace App\Models;

use App\Traits\HasStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockTransfer extends BaseModel
{
    use HasFactory, SoftDeletes, HasStatus;

    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_IN_TRANSIT = 'in_transit';
    const STATUS_RECEIVED = 'received';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'store_id',
        'transfer_number',
        'from_store_id',
        'to_store_id',
        'from_location_id',
        'to_location_id',
        'status',
        'current_status_id',
        'transfer_date',
        'expected_date',
        'received_date',
        'total_items',
        'total_quantity',
        'total_value',
        'notes',
        'shipping_details',
        'created_by',
        'approved_by',
        'received_by',
        'shipped_at',
        'received_at',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'expected_date' => 'date',
        'received_date' => 'date',
        'shipped_at' => 'datetime',
        'received_at' => 'datetime',
        'total_items' => 'integer',
        'total_quantity' => 'decimal:2',
        'total_value' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (StockTransfer $transfer) {
            if (empty($transfer->transfer_number)) {
                $transfer->transfer_number = self::generateTransferNumber();
            }
            if (empty($transfer->transfer_date)) {
                $transfer->transfer_date = now();
            }
            if (empty($transfer->created_by)) {
                $transfer->created_by = auth()->id();
            }
        });
    }

    public static function generateTransferNumber(): string
    {
        $prefix = 'TR';
        $date = now()->format('Ymd');
        $count = self::whereDate('created_at', today())->count() + 1;

        return $prefix . $date . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    // ==================== Relationships ====================

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function fromStore(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'from_store_id');
    }

    public function toStore(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'to_store_id');
    }

    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'from_location_id');
    }

    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'to_location_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockTransferItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    // ==================== Scopes ====================

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeInTransit($query)
    {
        return $query->where('status', self::STATUS_IN_TRANSIT);
    }

    public function scopeReceived($query)
    {
        return $query->where('status', self::STATUS_RECEIVED);
    }

    public function scopeFromLocation($query, int $locationId)
    {
        return $query->where('from_location_id', $locationId);
    }

    public function scopeToLocation($query, int $locationId)
    {
        return $query->where('to_location_id', $locationId);
    }

    // ==================== Status Methods ====================

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isInTransit(): bool
    {
        return $this->status === self::STATUS_IN_TRANSIT;
    }

    public function isReceived(): bool
    {
        return in_array($this->status, [self::STATUS_RECEIVED, self::STATUS_COMPLETED]);
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function canEdit(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_PENDING]);
    }

    public function canSend(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_PENDING, self::STATUS_APPROVED]);
    }

    public function canReceive(): bool
    {
        return $this->status === self::STATUS_IN_TRANSIT;
    }

    public function canCancel(): bool
    {
        return !in_array($this->status, [self::STATUS_RECEIVED, self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    /**
     * Define allowed status transitions.
     */
    public function getAllowedStatusTransitions(): array
    {
        return [
            'draft' => ['pending', 'cancelled'],
            'pending' => ['approved', 'in_transit', 'cancelled'],
            'approved' => ['in_transit', 'cancelled'],
            'in_transit' => ['received', 'completed', 'cancelled'],
            'received' => ['completed'],
            'completed' => [],
            'cancelled' => [],
        ];
    }

    // ==================== Helper Methods ====================

    /**
     * Recalculate totals from items
     */
    public function recalculateTotals(): void
    {
        $this->update([
            'total_items' => $this->items()->count(),
            'total_quantity' => $this->items()->sum('quantity_sent'),
            'total_value' => $this->items()->sum(\DB::raw('quantity_sent * unit_cost')),
        ]);
    }

    /**
     * Get status color for display
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'default',
            self::STATUS_PENDING => 'warning',
            self::STATUS_APPROVED => 'blue',
            self::STATUS_IN_TRANSIT => 'processing',
            self::STATUS_RECEIVED, self::STATUS_COMPLETED => 'success',
            self::STATUS_CANCELLED => 'error',
            default => 'default',
        };
    }

    /**
     * Get status label for display
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_IN_TRANSIT => 'In Transit',
            self::STATUS_RECEIVED => 'Received',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get source name (location or store)
     */
    public function getSourceNameAttribute(): string
    {
        if ($this->fromLocation) {
            return $this->fromLocation->name;
        }
        if ($this->fromStore) {
            return $this->fromStore->name;
        }
        return '-';
    }

    /**
     * Get destination name (location or store)
     */
    public function getDestinationNameAttribute(): string
    {
        if ($this->toLocation) {
            return $this->toLocation->name;
        }
        if ($this->toStore) {
            return $this->toStore->name;
        }
        return '-';
    }
}
