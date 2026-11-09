<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Inventory extends Model
{
  use HasFactory, SoftDeletes;
  use LogsActivity;
  
  /**
   * The attributes that are mass assignable.
   * @var array
   */
  
  protected $table    = 'inventory';
  protected $fillable = [
    'stock_id', 'order_id', 'product_id', 'cost', 'status', 'date'
  ];
  
  public function getActivitylogOptions() : LogOptions
  {
    return LogOptions::defaults()
                     ->useLogName( 'Inventory' )
                     ->logAll();
  }
  
  protected $casts = [
    'created_at' => 'datetime:Y-m-d H:i:s',
    'updated_at' => 'datetime:Y-m-d H:i:s',
  ];
  
  public function stock()
  {
    return $this->belongsTo( Stock::class );
  }
  
  public function product()
  {
    return $this->belongsTo( Product::class );
  }

  /**
   * Mark $qty units of a product as sold to an order: units in stock first, the rest
   * recorded as sold without stock (sales don't wait for stock-in).
   * $product is a cart row: ['id' => .., 'purchase_price' => ..].
   */
  public static function sell( int $orderId, array $product, int $qty, $date ) : void
  {
    if( $qty <= 0 ) {
      return;
    }
    $taken = self::where( 'product_id', $product[ 'id' ] )->where( 'status', 'Available' )
                 ->limit( $qty )->update( [ 'status' => 'Sold', 'order_id' => $orderId ] );

    for( $i = $taken; $i < $qty; $i++ ) {
      self::create( [
        'stock_id'   => 0,
        'order_id'   => $orderId,
        'product_id' => $product[ 'id' ],
        'cost'       => $product[ 'purchase_price' ],
        'status'     => 'Sold',
        'date'       => $date,
      ] );
    }
  }

  /**
   * Put back up to $qty units of a product that this order holds.
   */
  public static function release( int $orderId, int $productId, int $qty ) : void
  {
    if( $qty <= 0 ) {
      return;
    }
    self::where( 'product_id', $productId )->where( 'order_id', $orderId )->where( 'status', 'Sold' )
        ->limit( $qty )->update( [ 'status' => 'Available', 'order_id' => 0 ] );
  }
}
