<?php

namespace App\Models;

use App\Traits\HasStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderReturn extends BaseModel
{
    use HasFactory, SoftDeletes, HasStatus;

    protected $fillable = [
        'return_number',
        'order_id',
        'customer_id',
        'store_id',
        'processed_by',
        'type',
        'status',
        'current_status_id',
        'return_reason_id',
        'reason',
        'notes',
        'subtotal',
        'tax_amount',
        'total_amount',
        'refund_amount',
        'restocking_fee',
        'refund_method',
        'approved_at',
        'completed_at',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'restocking_fee' => 'decimal:2',
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function returnReason(): BelongsTo
    {
        return $this->belongsTo(ReturnReason::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderReturnItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    /**
     * Define allowed status transitions.
     */
    public function getAllowedStatusTransitions(): array
    {
        return [
            'pending' => ['approved', 'rejected'],
            'approved' => ['processed', 'rejected'],
            'processed' => [],
            'rejected' => [],
        ];
    }
}
