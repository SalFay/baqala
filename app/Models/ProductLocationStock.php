<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductLocationStock extends Model
{
    protected $table = 'product_location_stock';

    protected $fillable = [
        'product_id',
        'product_variant_id',
        'location_id',
        'quantity',
        'reserved_quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'reserved_quantity' => 'decimal:2',
    ];

    // ==================== Relationships ====================

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    // ==================== Scopes ====================

    public function scopeForProduct($query, int $productId, ?int $variantId = null)
    {
        $query->where('product_id', $productId);

        if ($variantId !== null) {
            $query->where('product_variant_id', $variantId);
        }

        return $query;
    }

    public function scopeForLocation($query, int $locationId)
    {
        return $query->where('location_id', $locationId);
    }

    public function scopeWithStock($query)
    {
        return $query->where('quantity', '>', 0);
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('quantity', '<=', 0);
    }

    // ==================== Helper Methods ====================

    /**
     * Get available quantity (quantity - reserved)
     */
    public function getAvailableQuantityAttribute(): float
    {
        return max(0, $this->quantity - $this->reserved_quantity);
    }

    /**
     * Add quantity to stock
     */
    public function addQuantity(float $quantity): bool
    {
        return $this->increment('quantity', $quantity);
    }

    /**
     * Deduct quantity from stock
     */
    public function deductQuantity(float $quantity): bool
    {
        if ($this->quantity < $quantity) {
            return false;
        }

        return $this->decrement('quantity', $quantity);
    }

    /**
     * Reserve quantity
     */
    public function reserve(float $quantity): bool
    {
        if ($this->available_quantity < $quantity) {
            return false;
        }

        return $this->increment('reserved_quantity', $quantity);
    }

    /**
     * Release reserved quantity
     */
    public function release(float $quantity): bool
    {
        $releaseAmount = min($quantity, $this->reserved_quantity);
        return $this->decrement('reserved_quantity', $releaseAmount);
    }

    /**
     * Get or create stock record
     */
    public static function getOrCreate(int $productId, int $locationId, ?int $variantId = null): self
    {
        return static::firstOrCreate(
            [
                'product_id' => $productId,
                'product_variant_id' => $variantId,
                'location_id' => $locationId,
            ],
            [
                'quantity' => 0,
                'reserved_quantity' => 0,
            ]
        );
    }

    /**
     * Transfer stock between locations
     */
    public static function transferStock(
        int $productId,
        int $fromLocationId,
        int $toLocationId,
        float $quantity,
        ?int $variantId = null
    ): bool {
        $fromStock = static::getOrCreate($productId, $fromLocationId, $variantId);
        $toStock = static::getOrCreate($productId, $toLocationId, $variantId);

        if ($fromStock->quantity < $quantity) {
            return false;
        }

        \DB::transaction(function () use ($fromStock, $toStock, $quantity) {
            $fromStock->deductQuantity($quantity);
            $toStock->addQuantity($quantity);
        });

        return true;
    }
}
