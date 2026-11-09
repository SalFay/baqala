<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'accounts';

    protected $fillable = [
        'id', 'party_type', 'party_id', 'debit', 'credit', 'comments', 'bank_id', 'cheque', 'discount'
    ];

    protected $with  = [ 'bank' ];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function partyable()
    {
        return $this->morphTo();
    }

    public function bank()
    {
        return $this->belongsTo( Bank::class );
    }

    /**
     * Ledger rows of one sale. They are linked to the order only by their comment
     * (older rows may say "Updated Order No.X" or "Order No.X Deleted").
     */
    public function scopeForOrder( $query, int $orderId )
    {
        return $query->where( 'party_type', Customer::class )->whereIn( 'comments', [
            'Order No.' . $orderId,
            'Updated Order No.' . $orderId,
            'Order No.' . $orderId . ' Deleted',
        ] );
    }

    /**
     * Post a sale to its customer's ledger: the total as debit, the amount paid as credit.
     */
    public static function postOrder( Order $order, $total, $paid, $bankId = 0 ) : void
    {
        self::create( [
            'party_type' => Customer::class,
            'party_id'   => $order->customer_id,
            'debit'      => $total,
            'bank_id'    => 0,
            'comments'   => 'Order No.' . $order->id,
        ] );

        if( $paid > 0 ) {
            self::create( [
                'party_type' => Customer::class,
                'party_id'   => $order->customer_id,
                'credit'     => $paid,
                'bank_id'    => $bankId,
                'comments'   => 'Order No.' . $order->id,
            ] );
        }
    }
}
