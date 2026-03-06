<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'variation_template_id',
        'sku',
        'barcode',
        'name',
        'attributes',
        'cost_price',
        'sale_price',
        'compare_price',
        'weight',
        'weight_unit',
        'image',
        'is_active',
    ];

    protected $casts = [
        'attributes' => 'array',
        'cost_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'weight' => 'decimal:3',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variationTemplate(): BelongsTo
    {
        return $this->belongsTo(VariationTemplate::class);
    }

    public function attributeValues(): HasMany
    {
        return $this->hasMany(ProductVariantAttribute::class);
    }

    public function storeInventories(): HasMany
    {
        return $this->hasMany(StoreInventory::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Helpers
    public function getAttributeString(): string
    {
        if (!$this->attributes) {
            return $this->name ?? '';
        }

        return collect($this->attributes)
            ->map(fn($value, $key) => "{$key}: {$value}")
            ->implode(', ');
    }

    public function getDisplayName(): string
    {
        if ($this->name) {
            return $this->name;
        }

        return $this->getAttributeString() ?: "Variant #{$this->id}";
    }

    public function getStockQuantity(): int
    {
        return $this->storeInventories()->sum('quantity');
    }
}
