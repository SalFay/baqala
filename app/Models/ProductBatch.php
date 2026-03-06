<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductBatch extends BaseModel
{
    use SoftDeletes;

    const STATUS_ACTIVE = 'active';
    const STATUS_LOW_STOCK = 'low_stock';
    const STATUS_OUT_OF_STOCK = 'out_of_stock';
    const STATUS_EXPIRED = 'expired';
    const STATUS_RECALLED = 'recalled';
    const STATUS_QUARANTINE = 'quarantine';

    protected $fillable = [
        'store_id',
        'product_id',
        'product_variant_id',
        'batch_number',
        'lot_number',
        'manufacturing_date',
        'expiry_date',
        'purchase_id',
        'purchase_price',
        'received_date',
        'quantity_purchased',
        'quantity_available',
        'quantity_sold',
        'quantity_damaged',
        'quantity_expired',
        'status',
        'supplier_batch_ref',
        'notes',
    ];

    protected $casts = [
        'manufacturing_date' => 'date',
        'expiry_date' => 'date',
        'received_date' => 'date',
        'purchase_price' => 'decimal:2',
        'quantity_purchased' => 'decimal:4',
        'quantity_available' => 'decimal:4',
        'quantity_sold' => 'decimal:4',
        'quantity_damaged' => 'decimal:4',
        'quantity_expired' => 'decimal:4',
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

    // ==================== Scopes ====================

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeInStock($query)
    {
        return $query->where('quantity_available', '>', 0)
            ->whereIn('status', [self::STATUS_ACTIVE, self::STATUS_LOW_STOCK]);
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now()->toDateString());
    }

    public function scopeNotExpired($query)
    {
        return $query->where('expiry_date', '>=', now()->toDateString());
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->whereBetween('expiry_date', [
            now()->toDateString(),
            now()->addDays($days)->toDateString(),
        ]);
    }

    public function scopeExpiringWithinDays($query, int $days)
    {
        return $query->whereBetween('expiry_date', [
            now()->toDateString(),
            now()->addDays($days)->toDateString(),
        ]);
    }

    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeForVariant($query, int $variantId)
    {
        return $query->where('product_variant_id', $variantId);
    }

    public function scopeSearch($query, ?string $term)
    {
        if (!$term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('batch_number', 'like', "%{$term}%")
                ->orWhere('lot_number', 'like', "%{$term}%")
                ->orWhere('supplier_batch_ref', 'like', "%{$term}%");
        });
    }

    public function scopeOrderByExpiry($query, string $direction = 'asc')
    {
        return $query->orderBy('expiry_date', $direction);
    }

    // FEFO - First Expiry First Out
    public function scopeFefo($query)
    {
        return $query->inStock()->orderByExpiry('asc');
    }

    // ==================== Helper Methods ====================

    public function isExpired(): bool
    {
        return $this->expiry_date < now()->toDateString();
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        if ($this->isExpired()) {
            return false;
        }

        return $this->expiry_date <= now()->addDays($days)->toDateString();
    }

    public function getDaysUntilExpiry(): int
    {
        return now()->startOfDay()->diffInDays($this->expiry_date, false);
    }

    public function getDaysFromManufacturing(): ?int
    {
        if (!$this->manufacturing_date) {
            return null;
        }

        return $this->manufacturing_date->diffInDays(now());
    }

    public function getShelfLifePercentage(): ?float
    {
        if (!$this->manufacturing_date) {
            return null;
        }

        $totalDays = $this->manufacturing_date->diffInDays($this->expiry_date);
        $daysUsed = $this->manufacturing_date->diffInDays(now());

        if ($totalDays <= 0) {
            return 0;
        }

        $remaining = max(0, 100 - (($daysUsed / $totalDays) * 100));
        return round($remaining, 2);
    }

    public function hasStock(): bool
    {
        return $this->quantity_available > 0;
    }

    public function canBeSold(): bool
    {
        return $this->hasStock()
            && !$this->isExpired()
            && in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_LOW_STOCK]);
    }

    /**
     * Deduct quantity from this batch (for sales)
     */
    public function deductQuantity(float $quantity): bool
    {
        if ($quantity > $this->quantity_available) {
            return false;
        }

        $this->quantity_available -= $quantity;
        $this->quantity_sold += $quantity;

        // Update status based on remaining quantity
        if ($this->quantity_available <= 0) {
            $this->status = self::STATUS_OUT_OF_STOCK;
        } elseif ($this->quantity_available <= ($this->quantity_purchased * 0.2)) {
            $this->status = self::STATUS_LOW_STOCK;
        }

        return $this->save();
    }

    /**
     * Add quantity back (for returns)
     */
    public function addQuantity(float $quantity): bool
    {
        $this->quantity_available += $quantity;
        $this->quantity_sold -= $quantity;

        // Update status
        if ($this->isExpired()) {
            $this->status = self::STATUS_EXPIRED;
        } elseif ($this->quantity_available > ($this->quantity_purchased * 0.2)) {
            $this->status = self::STATUS_ACTIVE;
        } else {
            $this->status = self::STATUS_LOW_STOCK;
        }

        return $this->save();
    }

    /**
     * Mark quantity as damaged
     */
    public function markAsDamaged(float $quantity): bool
    {
        if ($quantity > $this->quantity_available) {
            return false;
        }

        $this->quantity_available -= $quantity;
        $this->quantity_damaged += $quantity;

        if ($this->quantity_available <= 0) {
            $this->status = self::STATUS_OUT_OF_STOCK;
        }

        return $this->save();
    }

    /**
     * Mark batch as expired
     */
    public function markAsExpired(): bool
    {
        $this->quantity_expired = $this->quantity_available;
        $this->quantity_available = 0;
        $this->status = self::STATUS_EXPIRED;

        return $this->save();
    }

    /**
     * Recall the batch
     */
    public function recall(): bool
    {
        $this->status = self::STATUS_RECALLED;
        return $this->save();
    }

    /**
     * Put batch in quarantine
     */
    public function quarantine(): bool
    {
        $this->status = self::STATUS_QUARANTINE;
        return $this->save();
    }

    /**
     * Get status color for UI
     */
    public function getStatusColor(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'green',
            self::STATUS_LOW_STOCK => 'orange',
            self::STATUS_OUT_OF_STOCK => 'default',
            self::STATUS_EXPIRED => 'red',
            self::STATUS_RECALLED => 'red',
            self::STATUS_QUARANTINE => 'purple',
            default => 'default',
        };
    }

    /**
     * Get expiry status color
     */
    public function getExpiryStatusColor(): string
    {
        if ($this->isExpired()) {
            return 'red';
        }

        $days = $this->getDaysUntilExpiry();

        if ($days <= 7) {
            return 'red';
        } elseif ($days <= 30) {
            return 'orange';
        } elseif ($days <= 90) {
            return 'yellow';
        }

        return 'green';
    }

    /**
     * Get all possible statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_LOW_STOCK => 'Low Stock',
            self::STATUS_OUT_OF_STOCK => 'Out of Stock',
            self::STATUS_EXPIRED => 'Expired',
            self::STATUS_RECALLED => 'Recalled',
            self::STATUS_QUARANTINE => 'Quarantine',
        ];
    }
}
