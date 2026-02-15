<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTakeItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_take_id',
        'product_id',
        'product_variant_id',
        'sku',
        'barcode',
        'expected_quantity',
        'counted_quantity',
        'variance',
        'location',
        'notes',
        'counted_at',
        'counted_by',
    ];

    protected $casts = [
        'expected_quantity' => 'integer',
        'counted_quantity' => 'integer',
        'variance' => 'integer',
        'counted_at' => 'datetime',
    ];

    // Relationships
    public function stockTake(): BelongsTo
    {
        return $this->belongsTo(StockTake::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function countedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counted_by');
    }

    // Methods
    public function recordCount(int $quantity, int $userId): bool
    {
        $variance = $quantity - $this->expected_quantity;

        return $this->update([
            'counted_quantity' => $quantity,
            'variance' => $variance,
            'counted_at' => now(),
            'counted_by' => $userId,
        ]);
    }

    public function isCounted(): bool
    {
        return $this->counted_quantity !== null;
    }

    public function hasVariance(): bool
    {
        return $this->variance !== 0 && $this->variance !== null;
    }

    public function hasPositiveVariance(): bool
    {
        return $this->variance > 0;
    }

    public function hasNegativeVariance(): bool
    {
        return $this->variance < 0;
    }

    // Accessors
    public function getProductNameAttribute(): string
    {
        if ($this->variant) {
            return $this->product->name . ' - ' . $this->variant->name;
        }
        return $this->product?->name ?? 'Unknown Product';
    }

    public function getVarianceStatusAttribute(): string
    {
        if ($this->variance === null || $this->variance === 0) {
            return 'match';
        }
        return $this->variance > 0 ? 'over' : 'short';
    }

    public function getVarianceColorAttribute(): string
    {
        return match ($this->variance_status) {
            'over' => 'blue',
            'short' => 'red',
            default => 'green',
        };
    }
}
