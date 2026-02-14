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

    protected $fillable = [
        'transfer_number',
        'from_store_id',
        'to_store_id',
        'status',
        'current_status_id',
        'created_by',
        'approved_by',
        'received_by',
        'shipped_at',
        'received_at',
        'notes',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'shipped_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public function fromStore(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'from_store_id');
    }

    public function toStore(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'to_store_id');
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

    public function items(): HasMany
    {
        return $this->hasMany(StockTransferItem::class);
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    /**
     * Define allowed status transitions.
     */
    public function getAllowedStatusTransitions(): array
    {
        return [
            'pending' => ['approved', 'cancelled'],
            'approved' => ['in_transit', 'cancelled'],
            'in_transit' => ['completed', 'cancelled'],
            'completed' => [],
            'cancelled' => [],
        ];
    }
}
