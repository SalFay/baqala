<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quotation extends Model
{
    use SoftDeletes;

    const STATUS_DRAFT = 'draft';
    const STATUS_SENT = 'sent';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CONVERTED = 'converted';

    protected $fillable = [
        'store_id',
        'location_id',
        'quotation_number',
        'customer_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'valid_until',
        'status',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'discount_percent',
        'total',
        'notes',
        'terms_and_conditions',
        'converted_order_id',
        'converted_at',
        'created_by',
        'sent_by',
        'sent_at',
    ];

    protected $casts = [
        'valid_until' => 'date',
        'converted_at' => 'datetime',
        'sent_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($quotation) {
            if (empty($quotation->quotation_number)) {
                $quotation->quotation_number = self::generateNumber();
            }
        });
    }

    /**
     * Generate unique quotation number
     */
    public static function generateNumber(): string
    {
        $prefix = 'QT';
        $date = now()->format('Ymd');
        $lastQuotation = self::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        if ($lastQuotation && preg_match('/(\d+)$/', $lastQuotation->quotation_number, $matches)) {
            $sequence = (int)$matches[1] + 1;
        } else {
            $sequence = 1;
        }

        return sprintf('%s%s%04d', $prefix, $date, $sequence);
    }

    /**
     * Relationships
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class)->orderBy('sort_order');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sentBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function convertedOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'converted_order_id');
    }

    /**
     * Scopes
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeSent($query)
    {
        return $query->where('status', self::STATUS_SENT);
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', self::STATUS_ACCEPTED);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', [self::STATUS_DRAFT, self::STATUS_SENT]);
    }

    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_EXPIRED);
    }

    public function scopeExpiring($query)
    {
        return $query->where('status', self::STATUS_SENT)
            ->whereNotNull('valid_until')
            ->where('valid_until', '<=', now()->addDays(3));
    }

    /**
     * Attributes
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'default',
            self::STATUS_SENT => 'processing',
            self::STATUS_ACCEPTED => 'success',
            self::STATUS_REJECTED => 'error',
            self::STATUS_EXPIRED => 'warning',
            self::STATUS_CONVERTED => 'purple',
            default => 'default',
        };
    }

    public function getIsEditableAttribute(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SENT]);
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->valid_until && $this->valid_until->isPast();
    }

    public function getGuestNameAttribute(): string
    {
        return $this->customer?->name ?? $this->customer_name ?? 'Unknown';
    }

    /**
     * Actions
     */
    public function recalculate(): self
    {
        $subtotal = $this->items->sum(fn($item) => $item->quantity * $item->unit_price - $item->discount);
        $taxAmount = $this->items->sum('tax_amount');

        $discountAmount = $this->discount_percent
            ? ($subtotal * $this->discount_percent / 100)
            : ($this->discount_amount ?? 0);

        $this->subtotal = $subtotal;
        $this->tax_amount = $taxAmount;
        $this->discount_amount = $discountAmount;
        $this->total = $subtotal + $taxAmount - $discountAmount;
        $this->save();

        return $this;
    }

    public function markAsSent(): bool
    {
        if ($this->status !== self::STATUS_DRAFT) {
            return false;
        }

        $this->status = self::STATUS_SENT;
        $this->sent_by = auth()->id();
        $this->sent_at = now();
        $this->save();

        return true;
    }

    public function markAsAccepted(): bool
    {
        if (!in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SENT])) {
            return false;
        }

        $this->status = self::STATUS_ACCEPTED;
        $this->save();

        return true;
    }

    public function markAsRejected(): bool
    {
        if (!in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SENT])) {
            return false;
        }

        $this->status = self::STATUS_REJECTED;
        $this->save();

        return true;
    }

    public function checkExpiration(): bool
    {
        if ($this->status === self::STATUS_SENT && $this->is_expired) {
            $this->status = self::STATUS_EXPIRED;
            $this->save();
            return true;
        }

        return false;
    }

    /**
     * Convert quotation to order
     */
    public function convertToOrder(): ?Order
    {
        if ($this->status === self::STATUS_CONVERTED) {
            return $this->convertedOrder;
        }

        if (!in_array($this->status, [self::STATUS_ACCEPTED, self::STATUS_SENT])) {
            return null;
        }

        // Create order
        $order = Order::create([
            'store_id' => $this->store_id,
            'location_id' => $this->location_id,
            'customer_id' => $this->customer_id,
            'order_number' => Order::generateOrderNumber(),
            'order_type' => 'quotation',
            'subtotal' => $this->subtotal,
            'tax_amount' => $this->tax_amount,
            'discount_amount' => $this->discount_amount,
            'total' => $this->total,
            'notes' => $this->notes,
            'created_by' => auth()->id(),
        ]);

        // Copy items
        foreach ($this->items as $item) {
            $order->items()->create([
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'product_name' => $item->product_name,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'discount' => $item->discount,
                'tax_rate' => $item->tax_rate,
                'tax_amount' => $item->tax_amount,
                'line_total' => $item->line_total,
            ]);
        }

        // Update quotation
        $this->status = self::STATUS_CONVERTED;
        $this->converted_order_id = $order->id;
        $this->converted_at = now();
        $this->save();

        return $order;
    }
}
