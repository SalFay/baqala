<?php

namespace App\Models;

use App\Services\Pricing\PricingService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id', 'user_id', 'customer_id', 'coupon_id', 'coupon_code',
        'session_id', 'status', 'hold_name', 'subtotal', 'tax_amount',
        'discount', 'discount_type', 'discount_reason', 'coupon_discount',
        'total', 'loyalty_points_to_redeem', 'loyalty_discount',
        'notes', 'held_at', 'expires_at', 'selling_price_group_id',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'loyalty_discount' => 'decimal:2',
        'held_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    // Relationships
    public function store(): BelongsTo { return $this->belongsTo(Store::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function coupon(): BelongsTo { return $this->belongsTo(Coupon::class); }
    public function items(): HasMany { return $this->hasMany(CartItem::class); }
    public function sellingPriceGroup(): BelongsTo { return $this->belongsTo(SellingPriceGroup::class); }

    // Scopes
    public function scopeActive($q) { return $q->where('status', 'active'); }
    public function scopeHeld($q) { return $q->where('status', 'held'); }
    public function scopeForUser($q, int $userId) { return $q->where('user_id', $userId); }

    // Format for API/Frontend
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'items' => $this->items->map->toApiArray(),
            'customer' => $this->customer,
            'subtotal' => $this->subtotal ?? 0,
            'tax_amount' => $this->tax_amount ?? 0,
            'discount' => $this->discount ?? 0,
            'total' => $this->total ?? 0,
        ];
    }

    // Calculate totals from items
    public function recalculate(): self
    {
        // Refresh items relationship to get latest data including newly created items
        $this->load('items');

        $this->subtotal = $this->items->sum('line_total');
        $this->tax_amount = $this->items->sum('tax_amount');
        $this->total = max(0, $this->subtotal + $this->tax_amount - $this->getTotalDiscount());
        $this->save();
        return $this;
    }

    protected function getTotalDiscount(): float
    {
        $discount = $this->discount_type === 'percentage'
            ? ($this->subtotal * $this->discount) / 100
            : ($this->discount ?? 0);
        return $discount + ($this->loyalty_discount ?? 0) + ($this->coupon_discount ?? 0);
    }

    /**
     * Get configured PricingService with customer/price group context.
     */
    public function getPricingService(): PricingService
    {
        $service = app(PricingService::class);

        // Set customer context (this also sets customer group and its price group)
        if ($this->customer) {
            $service->setCustomer($this->customer);
        }

        // Override with cart's explicit price group if set
        if ($this->sellingPriceGroup) {
            $service->setPriceGroup($this->sellingPriceGroup);
        }

        return $service;
    }

    // Cart operations
    public function addItem(Product $product, int $qty = 1, ?ProductVariant $variant = null): CartItem
    {
        $item = $this->items()
            ->where('product_id', $product->id)
            ->where('product_variant_id', $variant?->id)
            ->first();

        if ($item) {
            $item->increment('quantity', $qty);
            $item->recalculate();
            return $item;
        }

        // Use PricingService for dynamic pricing
        $pricing = $this->getPricingService()->calculateItemPricing($product, $qty, $variant);

        return $this->items()->create([
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
            'sku' => $variant?->sku ?? $product->sku,
            'product_name' => $product->name,
            'variant_name' => $variant?->name,
            'quantity' => $qty,
            'unit_price' => $pricing['unit_price'],
            'original_price' => $pricing['base_price'],
            'purchase_price' => $variant?->cost_price ?? $product->cost_price ?? 0,
            'tax_rate' => $product->tax_rate ?? 0,
            'line_total' => $pricing['line_total'],
            'discount_amount' => $pricing['discount_amount'],
        ]);
    }

    /**
     * Recalculate all item prices (e.g., when customer or price group changes).
     */
    public function recalculatePrices(): self
    {
        $pricingService = $this->getPricingService();

        foreach ($this->items as $item) {
            $product = $item->product;
            $variant = $item->productVariant;

            if (!$product) continue;

            $pricing = $pricingService->calculateItemPricing($product, $item->quantity, $variant);

            $item->update([
                'unit_price' => $pricing['unit_price'],
                'original_price' => $pricing['base_price'],
                'line_total' => $pricing['line_total'],
                'discount_amount' => $pricing['discount_amount'],
            ]);
        }

        return $this->recalculate();
    }

    public function updateItemQty(int $itemId, int $qty): void
    {
        $item = $this->items()->findOrFail($itemId);
        $qty <= 0 ? $item->delete() : $item->update(['quantity' => $qty]) && $item->recalculate();
        $this->recalculate();
    }

    public function removeItem(int $itemId): void
    {
        $this->items()->where('id', $itemId)->delete();
        $this->recalculate();
    }

    public function clear(): void
    {
        $this->items()->delete();
        $this->update([
            'subtotal' => 0, 'tax_amount' => 0, 'discount' => 0,
            'discount_type' => null, 'total' => 0, 'customer_id' => null,
        ]);
    }

    public function hold(string $name): void
    {
        $this->update(['status' => 'held', 'hold_name' => $name, 'held_at' => now()]);
    }

    public function restore(): void
    {
        $this->update(['status' => 'active', 'hold_name' => null, 'held_at' => null]);
    }
}
