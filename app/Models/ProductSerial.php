<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductSerial extends BaseModel
{
    use SoftDeletes;

    const STATUS_AVAILABLE = 'available';
    const STATUS_RESERVED = 'reserved';
    const STATUS_SOLD = 'sold';
    const STATUS_RETURNED = 'returned';
    const STATUS_DAMAGED = 'damaged';
    const STATUS_LOST = 'lost';

    protected $fillable = [
        'store_id',
        'product_id',
        'product_variant_id',
        'serial_number',
        'imei',
        'imei2',
        'purchase_id',
        'purchase_price',
        'purchase_date',
        'order_id',
        'order_item_id',
        'sale_price',
        'sold_at',
        'status',
        'warranty_id',
        'warranty_start_date',
        'warranty_end_date',
        'color',
        'storage_capacity',
        'notes',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'purchase_date' => 'date',
        'sold_at' => 'datetime',
        'warranty_start_date' => 'date',
        'warranty_end_date' => 'date',
    ];

    // ==================== Relationships ====================

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    // ==================== Scopes ====================

    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_AVAILABLE);
    }

    public function scopeReserved($query)
    {
        return $query->where('status', self::STATUS_RESERVED);
    }

    public function scopeSold($query)
    {
        return $query->where('status', self::STATUS_SOLD);
    }

    public function scopeInStock($query)
    {
        return $query->whereIn('status', [self::STATUS_AVAILABLE, self::STATUS_RESERVED]);
    }

    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeForVariant($query, int $variantId)
    {
        return $query->where('product_variant_id', $variantId);
    }

    public function scopeWarrantyActive($query)
    {
        return $query->whereNotNull('warranty_end_date')
            ->where('warranty_end_date', '>=', now());
    }

    public function scopeWarrantyExpired($query)
    {
        return $query->whereNotNull('warranty_end_date')
            ->where('warranty_end_date', '<', now());
    }

    public function scopeSearch($query, ?string $term)
    {
        if (!$term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('serial_number', 'like', "%{$term}%")
                ->orWhere('imei', 'like', "%{$term}%")
                ->orWhere('imei2', 'like', "%{$term}%");
        });
    }

    // ==================== Helper Methods ====================

    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE;
    }

    public function isSold(): bool
    {
        return $this->status === self::STATUS_SOLD;
    }

    public function isReserved(): bool
    {
        return $this->status === self::STATUS_RESERVED;
    }

    public function canBeSold(): bool
    {
        return in_array($this->status, [self::STATUS_AVAILABLE, self::STATUS_RESERVED]);
    }

    public function hasWarranty(): bool
    {
        return $this->warranty_end_date !== null;
    }

    public function isWarrantyActive(): bool
    {
        if (!$this->hasWarranty()) {
            return false;
        }

        return $this->warranty_end_date >= now();
    }

    public function getWarrantyRemainingDays(): ?int
    {
        if (!$this->isWarrantyActive()) {
            return null;
        }

        return now()->diffInDays($this->warranty_end_date);
    }

    public function markAsSold(int $orderId, int $orderItemId, float $salePrice): bool
    {
        return $this->update([
            'status' => self::STATUS_SOLD,
            'order_id' => $orderId,
            'order_item_id' => $orderItemId,
            'sale_price' => $salePrice,
            'sold_at' => now(),
        ]);
    }

    public function markAsReturned(): bool
    {
        return $this->update([
            'status' => self::STATUS_RETURNED,
        ]);
    }

    public function markAsAvailable(): bool
    {
        return $this->update([
            'status' => self::STATUS_AVAILABLE,
            'order_id' => null,
            'order_item_id' => null,
            'sale_price' => null,
            'sold_at' => null,
        ]);
    }

    public function reserve(): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }

        return $this->update(['status' => self::STATUS_RESERVED]);
    }

    public function unreserve(): bool
    {
        if (!$this->isReserved()) {
            return false;
        }

        return $this->update(['status' => self::STATUS_AVAILABLE]);
    }

    /**
     * Get profit for this serial item
     */
    public function getProfit(): ?float
    {
        if ($this->sale_price === null || $this->purchase_price === null) {
            return null;
        }

        return $this->sale_price - $this->purchase_price;
    }

    /**
     * Get display identifier (serial or IMEI)
     */
    public function getDisplayIdentifier(): string
    {
        if ($this->imei) {
            return $this->imei;
        }

        return $this->serial_number;
    }

    /**
     * Get status badge color
     */
    public function getStatusColor(): string
    {
        return match ($this->status) {
            self::STATUS_AVAILABLE => 'green',
            self::STATUS_RESERVED => 'blue',
            self::STATUS_SOLD => 'default',
            self::STATUS_RETURNED => 'orange',
            self::STATUS_DAMAGED => 'red',
            self::STATUS_LOST => 'red',
            default => 'default',
        };
    }

    /**
     * Get all possible statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_AVAILABLE => 'Available',
            self::STATUS_RESERVED => 'Reserved',
            self::STATUS_SOLD => 'Sold',
            self::STATUS_RETURNED => 'Returned',
            self::STATUS_DAMAGED => 'Damaged',
            self::STATUS_LOST => 'Lost',
        ];
    }
}
