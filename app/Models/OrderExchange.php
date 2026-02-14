<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderExchange extends Model
{
    use HasFactory;

    protected $fillable = [
        'exchange_number',
        'order_return_id',
        'new_order_id',
        'price_difference',
        'difference_action',
        'payment_id',
        'notes',
    ];

    protected $casts = [
        'price_difference' => 'decimal:2',
    ];

    public function orderReturn(): BelongsTo
    {
        return $this->belongsTo(OrderReturn::class);
    }

    public function newOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'new_order_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
