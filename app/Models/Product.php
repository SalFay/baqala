<?php

namespace App\Models;

use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'vendor_id',
        'store_id',
        'name',
        'name_ar',
        'sku',
        'barcode',
        'type',
        'description',
        'image',
        'cost_price',
        'sale_price',
        'compare_price',
        'track_inventory',
        'low_stock_threshold',
        'weight',
        'weight_unit',
        'meta',
        'is_active',
    ];

    protected $casts = [
        'type' => ProductType::class,
        'cost_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'weight' => 'decimal:3',
        'track_inventory' => 'boolean',
        'low_stock_threshold' => 'integer',
        'is_active' => 'boolean',
        'meta' => 'array',
    ];

    protected $appends = ['display_name'];

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->sku)) {
                $product->sku = self::generateSku();
            }
        });
    }

    public static function generateSku(): string
    {
        $prefix = 'PRD';
        $count = self::withTrashed()->count() + 1;
        return $prefix . str_pad($count, 6, '0', STR_PAD_LEFT);
    }

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function storeInventories(): HasMany
    {
        return $this->hasMany(StoreInventory::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(ProductAttributeValue::class, 'product_attribute_product', 'product_id', 'attribute_value_id')
            ->withTimestamps();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForStore($query, int $storeId)
    {
        return $query->where(function ($q) use ($storeId) {
            $q->where('store_id', $storeId)
                ->orWhereNull('store_id');
        });
    }

    public function scopeInCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeSearch($query, ?string $term)
    {
        if (!$term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('name_ar', 'like', "%{$term}%")
                ->orWhere('sku', 'like', "%{$term}%")
                ->orWhere('barcode', 'like', "%{$term}%");
        });
    }

    public function scopeByBarcode($query, string $barcode)
    {
        return $query->where('barcode', $barcode);
    }

    public function scopeLowStock($query, ?int $storeId = null)
    {
        return $query->whereHas('storeInventories', function ($q) use ($storeId) {
            $q->when($storeId, fn($q) => $q->where('store_id', $storeId))
                ->whereColumn('quantity', '<=', 'products.low_stock_threshold');
        });
    }

    public function scopeSimple($query)
    {
        return $query->where('type', ProductType::SIMPLE);
    }

    public function scopeVariable($query)
    {
        return $query->where('type', ProductType::VARIABLE);
    }

    // Accessors
    public function getDisplayNameAttribute(): string
    {
        return $this->name_ar ?: $this->name;
    }

    public function getIsVariableAttribute(): bool
    {
        return $this->type === ProductType::VARIABLE;
    }

    public function getIsSimpleAttribute(): bool
    {
        return $this->type === ProductType::SIMPLE;
    }

    public function getProfitAttribute(): float
    {
        return $this->sale_price - $this->cost_price;
    }

    public function getProfitMarginAttribute(): float
    {
        if ($this->sale_price <= 0) {
            return 0;
        }
        return round(($this->profit / $this->sale_price) * 100, 2);
    }

    public function getHasDiscountAttribute(): bool
    {
        return $this->compare_price && $this->compare_price > $this->sale_price;
    }

    public function getDiscountPercentAttribute(): float
    {
        if (!$this->has_discount || $this->compare_price <= 0) {
            return 0;
        }
        return round((($this->compare_price - $this->sale_price) / $this->compare_price) * 100, 2);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }
        return asset('storage/' . $this->image);
    }

    // Methods
    public function getStockQuantity(?int $storeId = null): int
    {
        $query = $this->storeInventories();

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        return (int) $query->sum('quantity');
    }

    public function isInStock(?int $storeId = null, int $requiredQty = 1): bool
    {
        if (!$this->track_inventory) {
            return true;
        }

        return $this->getStockQuantity($storeId) >= $requiredQty;
    }

    public function isLowStock(?int $storeId = null): bool
    {
        if (!$this->track_inventory) {
            return false;
        }

        return $this->getStockQuantity($storeId) <= $this->low_stock_threshold;
    }

    public function getPrice(?ProductVariant $variant = null): float
    {
        if ($variant) {
            return $variant->sale_price ?? $this->sale_price;
        }

        return $this->sale_price;
    }

    public function getCost(?ProductVariant $variant = null): float
    {
        if ($variant) {
            return $variant->cost_price ?? $this->cost_price;
        }

        return $this->cost_price;
    }

    public function findVariantByBarcode(string $barcode): ?ProductVariant
    {
        return $this->variants()->where('barcode', $barcode)->first();
    }

    // Alias accessors for DRY (used by Cart/CartItem)
    public function getPriceAttribute(): float
    {
        return (float) $this->sale_price;
    }

    public function getCostAttribute(): float
    {
        return (float) $this->cost_price;
    }

    // Format for POS frontend
    public function toPosArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'price' => $this->price,
            'cost' => $this->cost,
            'image_url' => $this->image_url,
            'category_id' => $this->category_id,
            'category' => $this->category?->name,
            'in_stock' => $this->isInStock(),
            'stock_qty' => $this->track_inventory ? $this->getStockQuantity() : null,
            'has_variants' => $this->is_variable,
        ];
    }
}
