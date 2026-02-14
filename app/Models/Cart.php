<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'user_id',
        'customer_id',
        'session_id',
        'status',
        'hold_name',
        'subtotal',
        'tax_amount',
        'discount',
        'discount_type',
        'discount_reason',
        'total',
        'loyalty_points_to_redeem',
        'loyalty_discount',
        'notes',
        'held_at',
        'expires_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'loyalty_discount' => 'decimal:2',
        'loyalty_points_to_redeem' => 'integer',
        'held_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    // Relationships
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeHeld($query)
    {
        return $query->where('status', 'held');
    }

    public function scopeAbandoned($query)
    {
        return $query->where('status', 'abandoned');
    }

    public function scopeConverted($query)
    {
        return $query->where('status', 'converted');
    }

    public function scopeForStore($query, int $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }

    // Accessors
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    public function getIsHeldAttribute(): bool
    {
        return $this->status === 'held';
    }

    public function getIsConvertedAttribute(): bool
    {
        return $this->status === 'converted';
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getItemCountAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    public function getTotalItemsAttribute(): int
    {
        return $this->items->count();
    }

    public function getHasDiscountAttribute(): bool
    {
        return $this->discount > 0 || $this->loyalty_discount > 0;
    }

    public function getTotalDiscountAttribute(): float
    {
        $discount = 0;

        if ($this->discount > 0) {
            if ($this->discount_type === 'percentage') {
                $discount = ($this->subtotal * $this->discount) / 100;
            } else {
                $discount = $this->discount;
            }
        }

        return $discount + $this->loyalty_discount;
    }

    public function getGrandTotalAttribute(): float
    {
        return max(0, $this->subtotal + $this->tax_amount - $this->total_discount);
    }

    // Methods
    public function calculateTotals(): void
    {
        $subtotal = 0;
        $taxAmount = 0;

        foreach ($this->items as $item) {
            $subtotal += $item->line_total;
            $taxAmount += $item->tax_amount;
        }

        $this->subtotal = $subtotal;
        $this->tax_amount = $taxAmount;
        $this->total = $this->grand_total;
    }

    public function recalculateAndSave(): void
    {
        $this->calculateTotals();
        $this->save();
    }

    public function hold(string $name): void
    {
        $this->update([
            'status' => 'held',
            'hold_name' => $name,
            'held_at' => now(),
            'expires_at' => now()->addDays(7),
        ]);
    }

    public function restore(): void
    {
        $this->update([
            'status' => 'active',
            'hold_name' => null,
            'held_at' => null,
            'expires_at' => null,
        ]);
    }

    public function abandon(): void
    {
        $this->update(['status' => 'abandoned']);
    }

    public function markAsConverted(): void
    {
        $this->update(['status' => 'converted']);
    }

    public function clear(): void
    {
        $this->items()->delete();
        $this->update([
            'subtotal' => 0,
            'tax_amount' => 0,
            'discount' => 0,
            'discount_type' => null,
            'discount_reason' => null,
            'total' => 0,
            'loyalty_points_to_redeem' => 0,
            'loyalty_discount' => 0,
            'customer_id' => null,
            'notes' => null,
        ]);
    }

    public function setCustomer(?Customer $customer): void
    {
        $this->update(['customer_id' => $customer?->id]);
    }

    public function applyDiscount(float $amount, string $type, ?string $reason = null): void
    {
        $this->update([
            'discount' => $amount,
            'discount_type' => $type,
            'discount_reason' => $reason,
        ]);
        $this->recalculateAndSave();
    }

    public function removeDiscount(): void
    {
        $this->update([
            'discount' => 0,
            'discount_type' => null,
            'discount_reason' => null,
        ]);
        $this->recalculateAndSave();
    }

    public function setLoyaltyPoints(int $points): void
    {
        if (!$this->customer) {
            throw new \InvalidArgumentException('No customer selected');
        }

        $customerPoints = $this->customer->loyalty_points;
        if ($points > $customerPoints) {
            throw new \InvalidArgumentException('Customer does not have enough points');
        }

        // Calculate loyalty discount based on points
        $pointsValue = Setting::get('loyalty_points_value', 0.01);
        $loyaltyDiscount = $points * $pointsValue;

        $this->update([
            'loyalty_points_to_redeem' => $points,
            'loyalty_discount' => min($loyaltyDiscount, $this->subtotal),
        ]);
        $this->recalculateAndSave();
    }
}
