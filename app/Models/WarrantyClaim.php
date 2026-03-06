<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WarrantyClaim extends BaseModel
{
    use SoftDeletes;

    const STATUS_PENDING = 'pending';
    const STATUS_IN_REVIEW = 'in_review';
    const STATUS_APPROVED = 'approved';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELLED = 'cancelled';

    const FAULT_MANUFACTURING = 'manufacturing';
    const FAULT_USER_DAMAGE = 'user_damage';
    const FAULT_WEAR_AND_TEAR = 'wear_and_tear';
    const FAULT_UNKNOWN = 'unknown';
    const FAULT_OTHER = 'other';

    const RESOLUTION_REPAIR = 'repair';
    const RESOLUTION_REPLACE = 'replace';
    const RESOLUTION_REFUND = 'refund';
    const RESOLUTION_REJECTED = 'rejected';
    const RESOLUTION_PENDING = 'pending';

    const PRIORITY_NORMAL = 0;
    const PRIORITY_HIGH = 1;
    const PRIORITY_URGENT = 2;

    protected $fillable = [
        'store_id',
        'claim_number',
        'warranty_id',
        'product_serial_id',
        'order_id',
        'order_item_id',
        'customer_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'claim_date',
        'issue_description',
        'symptoms',
        'diagnosis',
        'fault_type',
        'resolution_type',
        'resolution_notes',
        'repair_cost',
        'replacement_serial_id',
        'status',
        'received_at',
        'reviewed_at',
        'resolved_at',
        'assigned_to',
        'internal_notes',
        'priority',
    ];

    protected $casts = [
        'symptoms' => 'array',
        'claim_date' => 'date',
        'received_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'resolved_at' => 'datetime',
        'repair_cost' => 'decimal:2',
        'priority' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (WarrantyClaim $claim) {
            if (empty($claim->claim_number)) {
                $claim->claim_number = self::generateClaimNumber();
            }
        });
    }

    public static function generateClaimNumber(): string
    {
        $prefix = 'WC';
        $date = now()->format('Ymd');
        $count = self::whereDate('created_at', now()->toDateString())->count() + 1;
        return $prefix . $date . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    // ==================== Relationships ====================

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function warranty(): BelongsTo
    {
        return $this->belongsTo(Warranty::class);
    }

    public function productSerial(): BelongsTo
    {
        return $this->belongsTo(ProductSerial::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function replacementSerial(): BelongsTo
    {
        return $this->belongsTo(ProductSerial::class, 'replacement_serial_id');
    }

    // ==================== Scopes ====================

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeInProgress($query)
    {
        return $query->whereIn('status', [
            self::STATUS_IN_REVIEW,
            self::STATUS_APPROVED,
            self::STATUS_IN_PROGRESS,
        ]);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeOpen($query)
    {
        return $query->whereNotIn('status', [
            self::STATUS_COMPLETED,
            self::STATUS_REJECTED,
            self::STATUS_CANCELLED,
        ]);
    }

    public function scopeUrgent($query)
    {
        return $query->where('priority', self::PRIORITY_URGENT);
    }

    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', [self::PRIORITY_HIGH, self::PRIORITY_URGENT]);
    }

    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    // ==================== Helper Methods ====================

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isOpen(): bool
    {
        return !in_array($this->status, [
            self::STATUS_COMPLETED,
            self::STATUS_REJECTED,
            self::STATUS_CANCELLED,
        ]);
    }

    public function isResolved(): bool
    {
        return in_array($this->status, [
            self::STATUS_COMPLETED,
            self::STATUS_REJECTED,
        ]);
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_IN_REVIEW,
        ]);
    }

    public function markAsReceived(): bool
    {
        return $this->update([
            'received_at' => now(),
            'status' => self::STATUS_IN_REVIEW,
        ]);
    }

    public function approve(): bool
    {
        return $this->update([
            'status' => self::STATUS_APPROVED,
            'reviewed_at' => now(),
        ]);
    }

    public function startProgress(): bool
    {
        return $this->update(['status' => self::STATUS_IN_PROGRESS]);
    }

    public function complete(string $resolutionType, ?string $notes = null): bool
    {
        return $this->update([
            'status' => self::STATUS_COMPLETED,
            'resolution_type' => $resolutionType,
            'resolution_notes' => $notes,
            'resolved_at' => now(),
        ]);
    }

    public function reject(?string $reason = null): bool
    {
        return $this->update([
            'status' => self::STATUS_REJECTED,
            'resolution_type' => self::RESOLUTION_REJECTED,
            'resolution_notes' => $reason,
            'resolved_at' => now(),
        ]);
    }

    public function cancel(): bool
    {
        return $this->update(['status' => self::STATUS_CANCELLED]);
    }

    public function assignTo(int $userId): bool
    {
        return $this->update(['assigned_to' => $userId]);
    }

    public function setPriority(int $priority): bool
    {
        return $this->update(['priority' => $priority]);
    }

    /**
     * Get status color for UI
     */
    public function getStatusColor(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'default',
            self::STATUS_IN_REVIEW => 'blue',
            self::STATUS_APPROVED => 'cyan',
            self::STATUS_IN_PROGRESS => 'orange',
            self::STATUS_COMPLETED => 'green',
            self::STATUS_REJECTED => 'red',
            self::STATUS_CANCELLED => 'default',
            default => 'default',
        };
    }

    /**
     * Get priority color for UI
     */
    public function getPriorityColor(): string
    {
        return match ($this->priority) {
            self::PRIORITY_URGENT => 'red',
            self::PRIORITY_HIGH => 'orange',
            default => 'default',
        };
    }

    /**
     * Get priority label
     */
    public function getPriorityLabel(): string
    {
        return match ($this->priority) {
            self::PRIORITY_URGENT => 'Urgent',
            self::PRIORITY_HIGH => 'High',
            default => 'Normal',
        };
    }

    /**
     * Get days since claim was filed
     */
    public function getDaysSinceClaim(): int
    {
        return $this->claim_date->diffInDays(now());
    }

    /**
     * Get all possible statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_IN_REVIEW => 'In Review',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    /**
     * Get all fault types
     */
    public static function getFaultTypes(): array
    {
        return [
            self::FAULT_MANUFACTURING => 'Manufacturing Defect',
            self::FAULT_USER_DAMAGE => 'User Damage',
            self::FAULT_WEAR_AND_TEAR => 'Wear and Tear',
            self::FAULT_UNKNOWN => 'Unknown',
            self::FAULT_OTHER => 'Other',
        ];
    }

    /**
     * Get all resolution types
     */
    public static function getResolutionTypes(): array
    {
        return [
            self::RESOLUTION_REPAIR => 'Repair',
            self::RESOLUTION_REPLACE => 'Replace',
            self::RESOLUTION_REFUND => 'Refund',
            self::RESOLUTION_REJECTED => 'Rejected',
            self::RESOLUTION_PENDING => 'Pending',
        ];
    }
}
