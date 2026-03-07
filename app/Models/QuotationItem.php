<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    protected $fillable = [
        'quotation_id',
        'product_id',
        'product_variant_id',
        'product_name',
        'product_sku',
        'quantity',
        'unit_price',
        'discount',
        'discount_percent',
        'tax_rate',
        'tax_amount',
        'line_total',
        'notes',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->calculateTotals();
        });

        static::saved(function ($item) {
            $item->quotation?->recalculate();
        });

        static::deleted(function ($item) {
            $item->quotation?->recalculate();
        });
    }

    /**
     * Relationships
     */
    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /**
     * Calculate totals
     */
    public function calculateTotals(): self
    {
        $subtotal = $this->quantity * $this->unit_price;

        // Calculate discount
        if ($this->discount_percent > 0) {
            $this->discount = $subtotal * ($this->discount_percent / 100);
        }

        $afterDiscount = $subtotal - ($this->discount ?? 0);

        // Calculate tax
        $this->tax_amount = $afterDiscount * ($this->tax_rate / 100);

        // Calculate line total
        $this->line_total = $afterDiscount + $this->tax_amount;

        return $this;
    }

    /**
     * Get the subtotal before discount and tax
     */
    public function getSubtotalAttribute(): float
    {
        return $this->quantity * $this->unit_price;
    }

    /**
     * Get the net amount after discount but before tax
     */
    public function getNetAmountAttribute(): float
    {
        return $this->subtotal - ($this->discount ?? 0);
    }
}
