<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'business_name',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'tax_number',
        'loyalty_card_number',
        'date_of_birth',
        'gender',
        'accepts_marketing',
        'preferences',
        'credit_limit',
        'credit_balance',
        'status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'accepts_marketing' => 'boolean',
        'preferences' => 'array',
        'credit_limit' => 'decimal:2',
        'credit_balance' => 'decimal:2',
    ];

    protected $appends = ['full_name'];

    protected static function booted(): void
    {
        static::creating(function (Customer $customer) {
            if (empty($customer->loyalty_card_number)) {
                $customer->loyalty_card_number = self::generateLoyaltyCardNumber();
            }
        });
    }

    public static function generateLoyaltyCardNumber(): string
    {
        $prefix = 'LC';
        $count = self::withTrashed()->count() + 1;
        return $prefix . str_pad($count, 8, '0', STR_PAD_LEFT);
    }

    // Relationships
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function loyalty(): HasOne
    {
        return $this->hasOne(CustomerLoyalty::class);
    }

    public function loyaltyTransactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    public function credits(): HasMany
    {
        return $this->hasMany(CustomerCredit::class);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', 'Suspended');
    }

    public function scopeSearch($query, ?string $term)
    {
        if (!$term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('first_name', 'like', "%{$term}%")
                ->orWhere('last_name', 'like', "%{$term}%")
                ->orWhere('business_name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%")
                ->orWhere('loyalty_card_number', 'like', "%{$term}%");
        });
    }

    public function scopeWithLoyalty($query)
    {
        return $query->whereHas('loyalty');
    }

    public function scopeHasCredit($query)
    {
        return $query->where('credit_balance', '>', 0);
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        $name = trim($this->first_name . ' ' . $this->last_name);
        return $name ?: $this->business_name ?? 'Unknown';
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->business_name) {
            return $this->business_name . ' (' . $this->full_name . ')';
        }
        return $this->full_name;
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'Active';
    }

    public function getIsSuspendedAttribute(): bool
    {
        return $this->status === 'Suspended';
    }

    public function getAvailableCreditAttribute(): float
    {
        return max(0, $this->credit_limit - $this->credit_balance);
    }

    public function getLoyaltyPointsAttribute(): int
    {
        return $this->loyalty?->points_balance ?? 0;
    }

    public function getLoyaltyTierAttribute(): ?string
    {
        return $this->loyalty?->tier?->name;
    }

    public function getTotalOrdersAttribute(): int
    {
        return $this->orders()->completed()->count();
    }

    public function getTotalSpentAttribute(): float
    {
        return (float) $this->orders()->completed()->sum('total');
    }

    // Methods
    public function canUseCredit(float $amount): bool
    {
        if ($this->is_suspended) {
            return false;
        }
        return $this->available_credit >= $amount;
    }

    public function addCredit(float $amount, ?string $reference = null, ?string $notes = null): CustomerCredit
    {
        $credit = $this->credits()->create([
            'amount' => $amount,
            'type' => 'credit',
            'reference' => $reference,
            'notes' => $notes,
            'balance_after' => $this->credit_balance + $amount,
        ]);

        $this->increment('credit_balance', $amount);

        return $credit;
    }

    public function useCredit(float $amount, ?string $reference = null, ?string $notes = null): CustomerCredit
    {
        if (!$this->canUseCredit($amount)) {
            throw new \InvalidArgumentException('Insufficient credit or customer suspended');
        }

        $credit = $this->credits()->create([
            'amount' => -$amount,
            'type' => 'debit',
            'reference' => $reference,
            'notes' => $notes,
            'balance_after' => $this->credit_balance - $amount,
        ]);

        $this->decrement('credit_balance', $amount);

        return $credit;
    }

    public function suspend(): void
    {
        $this->update(['status' => 'Suspended']);
    }

    public function activate(): void
    {
        $this->update(['status' => 'Active']);
    }

    public function hasEnrolledLoyalty(): bool
    {
        return $this->loyalty()->exists();
    }

    public function enrollInLoyalty(?int $tierId = null): CustomerLoyalty
    {
        if ($this->hasEnrolledLoyalty()) {
            throw new \InvalidArgumentException('Customer is already enrolled in loyalty program');
        }

        $tier = $tierId
            ? LoyaltyTier::find($tierId)
            : LoyaltyTier::orderBy('min_points')->first();

        return $this->loyalty()->create([
            'loyalty_tier_id' => $tier?->id,
            'points_balance' => 0,
            'lifetime_points' => 0,
            'enrolled_at' => now(),
        ]);
    }
}
