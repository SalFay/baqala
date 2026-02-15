<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id', 'product_id', 'product_variant_id', 'sku', 'product_name',
        'variant_name', 'quantity', 'unit_price', 'purchase_price', 'discount',
        'discount_type', 'tax_rate', 'tax_amount', 'line_total', 'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function cart(): BelongsTo { return $this->belongsTo(Cart::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function variant(): BelongsTo { return $this->belongsTo(ProductVariant::class, 'product_variant_id'); }

    // Format for API/Frontend
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_variant_id' => $this->product_variant_id,
            'product' => $this->product?->only(['id', 'name', 'sku', 'price', 'image_url'])
                ?? ['name' => $this->product_name, 'sku' => $this->sku],
            'product_name' => $this->product_name,
            'variant_name' => $this->variant_name,
            'sku' => $this->sku,
            'quantity' => (int) $this->quantity,
            'unit_price' => (float) $this->unit_price,
            'line_total' => (float) $this->line_total,
            'tax_amount' => (float) ($this->tax_amount ?? 0),
            'discount' => (float) ($this->discount ?? 0),
        ];
    }

    public function recalculate(): self
    {
        $subtotal = $this->unit_price * $this->quantity;
        $discount = $this->discount_type === 'percentage'
            ? ($subtotal * $this->discount) / 100
            : ($this->discount ?? 0);

        $afterDiscount = $subtotal - $discount;
        $this->tax_amount = round(($afterDiscount * ($this->tax_rate ?? 0)) / 100, 2);
        $this->line_total = round($afterDiscount + $this->tax_amount, 2);
        $this->save();
        return $this;
    }
}
