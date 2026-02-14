<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'store_id',
        'customer_id',
        'user_id',
        'status',
        'payment_status',
        'payment_type',
        'date',
        'sub_total',
        'subtotal',
        'tax_amount',
        'discount',
        'discount_type',
        'discount_percent',
        'total',
        'paid_amount',
        'change_amount',
        'vat',
        'loyalty_points_earned',
        'loyalty_points_redeemed',
        'loyalty_discount',
        'customer_name',
        'cashier_name',
        'notes',
        'completed_at',
        'invoice_no',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
        'payment_status' => PaymentStatus::class,
        'date' => 'datetime',
        'completed_at' => 'datetime',
        'sub_total' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'vat' => 'decimal:2',
        'loyalty_discount' => 'decimal:2',
        'loyalty_points_earned' => 'integer',
        'loyalty_points_redeemed' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber($order->store_id);
            }
            if (empty($order->invoice_no)) {
                $order->invoice_no = self::generateInvoiceNumber($order->store_id);
            }
        });
    }

    public static function generateOrderNumber(?int $storeId = null): string
    {
        $prefix = 'ORD';
        if ($storeId) {
            $prefix .= str_pad($storeId, 2, '0', STR_PAD_LEFT);
        }
        $date = now()->format('Ymd');
        $count = self::whereDate('created_at', today())
            ->when($storeId, fn($q) => $q->where('store_id', $storeId))
            ->count() + 1;

        return $prefix . $date . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public static function generateInvoiceNumber(?int $storeId = null): string
    {
        $prefix = 'INV';
        if ($storeId) {
            $prefix .= str_pad($storeId, 2, '0', STR_PAD_LEFT);
        }
        $year = now()->format('Y');
        $count = self::whereYear('created_at', now()->year)
            ->when($storeId, fn($q) => $q->where('store_id', $storeId))
            ->count() + 1;

        return $prefix . $year . str_pad($count, 6, '0', STR_PAD_LEFT);
    }

    // Relationships
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(OrderReturn::class);
    }

    // Scopes
    public function scopeForStore($query, int $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', OrderStatus::COMPLETED);
    }

    public function scopePending($query)
    {
        return $query->where('status', OrderStatus::PENDING);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', OrderStatus::CANCELLED);
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', PaymentStatus::PAID);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }

    public function scopeDateRange($query, $from, $to)
    {
        return $query->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to));
    }

    public function scopeSearch($query, ?string $term)
    {
        if (!$term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('order_number', 'like', "%{$term}%")
                ->orWhere('invoice_no', 'like', "%{$term}%")
                ->orWhere('customer_name', 'like', "%{$term}%");
        });
    }

    // Accessors
    public function getIsCompletedAttribute(): bool
    {
        return $this->status === OrderStatus::COMPLETED;
    }

    public function getIsCancelledAttribute(): bool
    {
        return $this->status === OrderStatus::CANCELLED;
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->payment_status === PaymentStatus::PAID;
    }

    public function getCanBeCancelledAttribute(): bool
    {
        return !in_array($this->status, [
            OrderStatus::CANCELLED,
            OrderStatus::REFUNDED,
        ]);
    }

    public function getCanBeRefundedAttribute(): bool
    {
        return $this->status === OrderStatus::COMPLETED
            && $this->payment_status === PaymentStatus::PAID;
    }

    public function getItemCountAttribute(): int
    {
        return $this->items->sum('quantity') ?? $this->items->sum('stock') ?? 0;
    }

    public function getDisplayStatusAttribute(): string
    {
        return $this->status->label();
    }

    public function getStatusColorAttribute(): string
    {
        return $this->status->color();
    }

    // Methods
    public function calculateTotals(): void
    {
        $this->sub_total = $this->items->sum('line_total');
        $this->tax_amount = $this->items->sum('tax_amount');
        $this->total = $this->sub_total + $this->tax_amount - $this->discount - $this->loyalty_discount;
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => OrderStatus::COMPLETED,
            'completed_at' => now(),
        ]);
    }

    public function markAsCancelled(): void
    {
        $this->update([
            'status' => OrderStatus::CANCELLED,
        ]);
    }

    public function markAsPaid(): void
    {
        $this->update([
            'payment_status' => PaymentStatus::PAID,
        ]);
    }
}
