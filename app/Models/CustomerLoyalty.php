<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerLoyalty extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'loyalty_tier_id',
        'card_number',
        'points_balance',
        'points_earned_total',
        'points_redeemed_total',
        'points_expired_total',
        'lifetime_spend',
        'last_activity_at',
        'tier_expires_at',
        'is_active',
    ];

    protected $casts = [
        'points_balance' => 'integer',
        'points_earned_total' => 'integer',
        'points_redeemed_total' => 'integer',
        'points_expired_total' => 'integer',
        'lifetime_spend' => 'decimal:2',
        'last_activity_at' => 'datetime',
        'tier_expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function tier(): BelongsTo
    {
        return $this->belongsTo(LoyaltyTier::class, 'loyalty_tier_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }
}
