<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_return_id',
        'order_item_id',
        'product_id',
        'product_variant_id',
        'quantity',
        'unit_price',
        'tax_amount',
        'total',
        'condition',
        'restock',
        'reason',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'restock' => 'boolean',
    ];

    public function orderReturn(): BelongsTo
    {
        return $this->belongsTo(OrderReturn::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function isSellable(): bool
    {
        return $this->condition === 'sellable';
    }

    public function isDamaged(): bool
    {
        return $this->condition === 'damaged';
    }

    public function isDefective(): bool
    {
        return $this->condition === 'defective';
    }

    public function getProductNameAttribute(): string
    {
        if ($this->variant) {
            return $this->product->name . ' - ' . $this->variant->name;
        }
        return $this->product?->name ?? 'Unknown Product';
    }
}
