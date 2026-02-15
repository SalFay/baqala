<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'sku',
        'product_name',
        'variant_name',
        'quantity',
        'unit_price',
        'cost_price',
        'discount',
        'discount_percent',
        'tax_rate',
        'tax_amount',
        'line_total',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function returnItems()
    {
        return $this->hasMany(OrderReturnItem::class);
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->variant_name) {
            return $this->product_name . ' - ' . $this->variant_name;
        }
        return $this->product_name ?? 'Unknown Product';
    }

    public function getSalePriceAttribute(): float
    {
        return (float) $this->unit_price;
    }

    public function getStockAttribute(): int
    {
        return (int) $this->quantity;
    }

    public function getReturnedQuantityAttribute(): int
    {
        return (int) $this->returnItems()
            ->whereHas('orderReturn', function ($q) {
                $q->whereIn('status', ['approved', 'processed', 'completed']);
            })
            ->sum('quantity');
    }

    public function getReturnableQuantityAttribute(): int
    {
        return max(0, $this->quantity - $this->returned_quantity);
    }
}
