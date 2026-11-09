<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Order extends Model
{
  use HasFactory, SoftDeletes;
  use LogsActivity;

  /**
   * The attributes that are mass assignable.
   * @var array
   */
  protected $fillable = [
    'customer_id', 'payment_type', 'invoice_no', 'date',
    'total', 'sub_total', 'discount', 'discount_type', 'delivery_charges', 'credit', 'debit', 'vat',
    'customer_name', 'cashier_name'
  ];

  protected $with = [ 'customer', 'items' ];

  protected $casts = [
    'created_at' => 'datetime:Y-m-d H:i:s',
    'updated_at' => 'datetime:Y-m-d H:i:s',
  ];

  public function customer()
  {
    return $this->belongsTo( Customer::class );
  }

  public function getActivitylogOptions() : LogOptions
  {
    return LogOptions::defaults()
                     ->useLogName( 'Order' )
                     ->logAll();
  }

  public function items()
  {
    return $this->hasMany( OrderItem::class );
  }

  /**
   * SQL for the discount actually given, in rupees. Taken from the order's own totals
   * so it is right whether the cashier entered Rs or % (discount_type 'rupee' / 'per').
   */
  public const DISCOUNT_SQL = 'GREATEST(0, sub_total + COALESCE(delivery_charges, 0) - total)';

  public function getDiscountAmountAttribute() : float
  {
    return max( 0, round( $this->sub_total + ( $this->delivery_charges ?? 0 ) - $this->total, 2 ) );
  }
}
