<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LoyaltyTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_loyalty_id',
        'store_id',
        'type',
        'points',
        'points_balance_after',
        'reference_type',
        'reference_id',
        'description',
        'created_by',
        'expires_at',
    ];

    protected $casts = [
        'points' => 'integer',
        'points_balance_after' => 'integer',
        'expires_at' => 'datetime',
    ];

    public function customerLoyalty(): BelongsTo
    {
        return $this->belongsTo(CustomerLoyalty::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
