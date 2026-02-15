<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Credit extends Model
{
    use HasFactory;

    protected $fillable = [
        'creditable_type',
        'creditable_id',
        'amount',
        'type',
        'reference',
        'notes',
        'balance_after',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    // Relationships
    public function creditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Type checkers
    public function isCredit(): bool
    {
        return $this->type === 'credit';
    }

    public function isDebit(): bool
    {
        return $this->type === 'debit';
    }

    // Scopes
    public function scopeCredits($query)
    {
        return $query->where('type', 'credit');
    }

    public function scopeDebits($query)
    {
        return $query->where('type', 'debit');
    }

    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('creditable_type', Customer::class)
                     ->where('creditable_id', $customerId);
    }

    public function scopeForVendor($query, int $vendorId)
    {
        return $query->where('creditable_type', Vendor::class)
                     ->where('creditable_id', $vendorId);
    }

    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }
}
