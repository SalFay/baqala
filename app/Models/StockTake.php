<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockTake extends BaseModel
{
    use HasFactory, SoftDeletes;

    const STATUS_DRAFT = 'draft';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    const TYPE_FULL = 'full';
    const TYPE_PARTIAL = 'partial';
    const TYPE_CATEGORY = 'category';
    const TYPE_LOCATION = 'location';

    protected $fillable = [
        'stock_take_number',
        'store_id',
        'created_by',
        'completed_by',
        'status',
        'type',
        'category_id',
        'location',
        'notes',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (StockTake $stockTake) {
            if (empty($stockTake->stock_take_number)) {
                $stockTake->stock_take_number = self::generateStockTakeNumber();
            }
        });
    }

    public static function generateStockTakeNumber(): string
    {
        $prefix = 'STK';
        $date = now()->format('Ymd');
        $count = self::whereDate('created_at', today())->count() + 1;
        return $prefix . $date . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    // Relationships
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockTakeItem::class);
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

    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    // Status checks
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function canBeStarted(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function canBeCounted(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function canBeCompleted(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_IN_PROGRESS]);
    }

    // Actions
    public function start(): bool
    {
        if (!$this->canBeStarted()) {
            return false;
        }

        return $this->update([
            'status' => self::STATUS_IN_PROGRESS,
            'started_at' => now(),
        ]);
    }

    public function complete(int $userId): bool
    {
        if (!$this->canBeCompleted()) {
            return false;
        }

        return $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_by' => $userId,
            'completed_at' => now(),
        ]);
    }

    public function cancel(): bool
    {
        if (!$this->canBeCancelled()) {
            return false;
        }

        return $this->update([
            'status' => self::STATUS_CANCELLED,
        ]);
    }

    // Accessors
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'default',
            self::STATUS_IN_PROGRESS => 'processing',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_CANCELLED => 'error',
            default => 'default',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    public function getTotalItemsAttribute(): int
    {
        return $this->items()->count();
    }

    public function getCountedItemsAttribute(): int
    {
        return $this->items()->whereNotNull('counted_quantity')->count();
    }

    public function getProgressAttribute(): float
    {
        $total = $this->total_items;
        if ($total === 0) {
            return 0;
        }
        return round(($this->counted_items / $total) * 100, 1);
    }

    public function getTotalVarianceAttribute(): int
    {
        return (int) $this->items()->sum('variance');
    }

    public function getPositiveVarianceCountAttribute(): int
    {
        return $this->items()->where('variance', '>', 0)->count();
    }

    public function getNegativeVarianceCountAttribute(): int
    {
        return $this->items()->where('variance', '<', 0)->count();
    }

    // API representation
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'stock_take_number' => $this->stock_take_number,
            'type' => $this->type,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'status_color' => $this->status_color,
            'category' => $this->category?->name,
            'location' => $this->location,
            'notes' => $this->notes,
            'total_items' => $this->total_items,
            'counted_items' => $this->counted_items,
            'progress' => $this->progress,
            'total_variance' => $this->total_variance,
            'positive_variance_count' => $this->positive_variance_count,
            'negative_variance_count' => $this->negative_variance_count,
            'created_by' => $this->creator?->name,
            'completed_by' => $this->completedByUser?->name,
            'started_at' => $this->started_at?->format('Y-m-d H:i'),
            'completed_at' => $this->completed_at?->format('Y-m-d H:i'),
            'created_at' => $this->created_at->format('Y-m-d H:i'),
        ];
    }
}
