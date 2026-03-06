<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'name',
        'code',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'phone',
        'email',
        'is_main',
        'is_active',
        'settings',
        'selling_price_group_id',
        'invoice_prefix',
        'invoice_counter',
    ];

    protected $casts = [
        'is_main' => 'boolean',
        'is_active' => 'boolean',
        'settings' => 'array',
        'invoice_counter' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Location $location) {
            if (empty($location->code)) {
                $location->code = strtoupper(substr($location->name, 0, 3)) . rand(100, 999);
            }
        });
    }

    // ==================== Relationships ====================

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function sellingPriceGroup(): BelongsTo
    {
        return $this->belongsTo(SellingPriceGroup::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function cashRegisters(): HasMany
    {
        return $this->hasMany(CashRegister::class);
    }

    public function outgoingTransfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'from_location_id');
    }

    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'to_location_id');
    }

    public function productStock(): HasMany
    {
        return $this->hasMany(ProductLocationStock::class);
    }

    // ==================== Scopes ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeMain($query)
    {
        return $query->where('is_main', true);
    }

    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }

    // ==================== Helper Methods ====================

    public function isMain(): bool
    {
        return $this->is_main;
    }

    /**
     * Get full address
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get setting value
     */
    public function getSetting(string $key, $default = null)
    {
        return data_get($this->settings, $key, $default);
    }

    /**
     * Set a setting value
     */
    public function setSetting(string $key, $value): bool
    {
        $settings = $this->settings ?? [];
        data_set($settings, $key, $value);
        return $this->update(['settings' => $settings]);
    }

    /**
     * Generate next invoice number
     */
    public function generateInvoiceNumber(): string
    {
        $this->increment('invoice_counter');
        $counter = str_pad($this->invoice_counter, 6, '0', STR_PAD_LEFT);

        return ($this->invoice_prefix ?? $this->code ?? 'INV') . '-' . $counter;
    }

    /**
     * Get stock quantity for a product at this location
     */
    public function getProductStock(int $productId, ?int $variantId = null): float
    {
        $query = $this->productStock()
            ->where('product_id', $productId);

        if ($variantId) {
            $query->where('product_variant_id', $variantId);
        } else {
            $query->whereNull('product_variant_id');
        }

        return $query->value('quantity') ?? 0;
    }

    /**
     * Get available stock (quantity - reserved)
     */
    public function getAvailableStock(int $productId, ?int $variantId = null): float
    {
        $query = $this->productStock()
            ->where('product_id', $productId);

        if ($variantId) {
            $query->where('product_variant_id', $variantId);
        } else {
            $query->whereNull('product_variant_id');
        }

        $stock = $query->first();

        if (!$stock) {
            return 0;
        }

        return max(0, $stock->quantity - $stock->reserved_quantity);
    }
}
