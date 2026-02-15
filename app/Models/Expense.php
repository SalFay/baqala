<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends BaseModel
{
    use HasFactory, SoftDeletes;

    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PAID = 'paid';

    const FREQUENCY_DAILY = 'daily';
    const FREQUENCY_WEEKLY = 'weekly';
    const FREQUENCY_MONTHLY = 'monthly';
    const FREQUENCY_YEARLY = 'yearly';

    protected $fillable = [
        'expense_number',
        'store_id',
        'expense_category_id',
        'account_id',
        'vendor_id',
        'created_by',
        'approved_by',
        'reference_number',
        'expense_date',
        'amount',
        'tax_amount',
        'total',
        'payment_method',
        'status',
        'description',
        'receipt_path',
        'is_recurring',
        'recurring_frequency',
        'next_occurrence_date',
        'rejection_reason',
        'approved_at',
        'paid_at',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'is_recurring' => 'boolean',
        'next_occurrence_date' => 'date',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Expense $expense) {
            if (empty($expense->expense_number)) {
                $expense->expense_number = self::generateExpenseNumber();
            }
            if (empty($expense->total)) {
                $expense->total = $expense->amount + $expense->tax_amount;
            }
        });
    }

    public static function generateExpenseNumber(): string
    {
        $prefix = 'EXP';
        $date = now()->format('Ymd');
        $count = self::whereDate('created_at', today())->count() + 1;
        return $prefix . $date . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    // Relationships
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeForStore($query, int $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('expense_date', [$startDate, $endDate]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year);
    }

    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    public function scopeSearch($query, ?string $term)
    {
        if (!$term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('expense_number', 'like', "%{$term}%")
                ->orWhere('reference_number', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }

    // Accessors
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'default',
            self::STATUS_PENDING => 'warning',
            self::STATUS_APPROVED => 'success',
            self::STATUS_REJECTED => 'error',
            self::STATUS_PAID => 'blue',
            default => 'default',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PENDING => 'Pending Approval',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_PAID => 'Paid',
            default => ucfirst($this->status),
        };
    }

    public function getReceiptUrlAttribute(): ?string
    {
        if (!$this->receipt_path) {
            return null;
        }
        return asset('storage/' . $this->receipt_path);
    }

    // Status checks
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function canBeApproved(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_PENDING]);
    }

    public function canBeRejected(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_PENDING]);
    }

    public function canBePaid(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_PENDING, self::STATUS_REJECTED]);
    }

    // Actions
    public function approve(int $userId): bool
    {
        if (!$this->canBeApproved()) {
            return false;
        }

        return $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_by' => $userId,
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);
    }

    public function reject(int $userId, string $reason): bool
    {
        if (!$this->canBeRejected()) {
            return false;
        }

        return $this->update([
            'status' => self::STATUS_REJECTED,
            'approved_by' => $userId,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    public function markAsPaid(): bool
    {
        if (!$this->canBePaid()) {
            return false;
        }

        return $this->update([
            'status' => self::STATUS_PAID,
            'paid_at' => now(),
        ]);
    }

    public function calculateNextOccurrence(): ?string
    {
        if (!$this->is_recurring || !$this->recurring_frequency) {
            return null;
        }

        $baseDate = $this->next_occurrence_date ?? $this->expense_date;

        return match ($this->recurring_frequency) {
            self::FREQUENCY_DAILY => $baseDate->addDay()->format('Y-m-d'),
            self::FREQUENCY_WEEKLY => $baseDate->addWeek()->format('Y-m-d'),
            self::FREQUENCY_MONTHLY => $baseDate->addMonth()->format('Y-m-d'),
            self::FREQUENCY_YEARLY => $baseDate->addYear()->format('Y-m-d'),
            default => null,
        };
    }

    // Array representation for API
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'expense_number' => $this->expense_number,
            'expense_date' => $this->expense_date->format('Y-m-d'),
            'category' => $this->category?->name,
            'category_id' => $this->expense_category_id,
            'vendor' => $this->vendor?->name,
            'vendor_id' => $this->vendor_id,
            'amount' => $this->amount,
            'tax_amount' => $this->tax_amount,
            'total' => $this->total,
            'payment_method' => $this->payment_method,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'status_color' => $this->status_color,
            'description' => $this->description,
            'reference_number' => $this->reference_number,
            'receipt_url' => $this->receipt_url,
            'is_recurring' => $this->is_recurring,
            'recurring_frequency' => $this->recurring_frequency,
            'created_by' => $this->creator?->first_name . ' ' . $this->creator?->last_name,
            'approved_by' => $this->approver ? $this->approver->first_name . ' ' . $this->approver->last_name : null,
            'approved_at' => $this->approved_at?->format('Y-m-d H:i'),
            'created_at' => $this->created_at->format('Y-m-d H:i'),
        ];
    }
}
