<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransferItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_transfer_id',
        'product_id',
        'product_variant_id',
        'batch_id',
        'serial_id',
        'quantity_requested',
        'quantity_sent',
        'quantity_received',
        'unit_cost',
        'notes',
    ];

    protected $casts = [
        'quantity_requested' => 'decimal:2',
        'quantity_sent' => 'decimal:2',
        'quantity_received' => 'decimal:2',
        'unit_cost' => 'decimal:2',
    ];

    // ==================== Relationships ====================

    public function stockTransfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductBatch::class, 'batch_id');
    }

    public function serial(): BelongsTo
    {
        return $this->belongsTo(ProductSerial::class, 'serial_id');
    }

    // ==================== Helper Methods ====================

    /**
     * Get line total
     */
    public function getLineTotalAttribute(): float
    {
        return ($this->quantity_sent ?? 0) * ($this->unit_cost ?? 0);
    }

    /**
     * Check if item was fully received
     */
    public function isFullyReceived(): bool
    {
        if ($this->quantity_received === null) {
            return false;
        }

        return $this->quantity_received >= $this->quantity_sent;
    }

    /**
     * Check if item was partially received
     */
    public function isPartiallyReceived(): bool
    {
        if ($this->quantity_received === null) {
            return false;
        }

        return $this->quantity_received > 0 && $this->quantity_received < $this->quantity_sent;
    }

    /**
     * Get variance (sent - received)
     */
    public function getVarianceAttribute(): ?float
    {
        if ($this->quantity_received === null) {
            return null;
        }

        return $this->quantity_sent - $this->quantity_received;
    }

    /**
     * Get product name with variant
     */
    public function getProductNameAttribute(): string
    {
        $name = $this->product?->name ?? 'Unknown Product';

        if ($this->variant) {
            $name .= ' - ' . $this->variant->name;
        }

        return $name;
    }
}
