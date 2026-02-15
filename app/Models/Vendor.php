<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'contact_name',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'tax_number',
        'balance',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function credits(): MorphMany
    {
        return $this->morphMany(Credit::class, 'creditable');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Credit methods
    public function addCredit(float $amount, ?string $reference = null, ?string $notes = null): Credit
    {
        $credit = $this->credits()->create([
            'amount' => $amount,
            'type' => 'credit',
            'reference' => $reference,
            'notes' => $notes,
            'balance_after' => ($this->balance ?? 0) + $amount,
            'created_by' => auth()->id(),
        ]);

        $this->increment('balance', $amount);

        return $credit;
    }

    public function addDebit(float $amount, ?string $reference = null, ?string $notes = null): Credit
    {
        $credit = $this->credits()->create([
            'amount' => abs($amount),
            'type' => 'debit',
            'reference' => $reference,
            'notes' => $notes,
            'balance_after' => ($this->balance ?? 0) - abs($amount),
            'created_by' => auth()->id(),
        ]);

        $this->decrement('balance', abs($amount));

        return $credit;
    }
}
