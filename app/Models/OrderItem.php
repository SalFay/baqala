<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Yajra\DataTables\Html\Editor\Fields\BelongsTo;

class OrderItem extends Model
{
  use HasFactory, SoftDeletes;
  use LogsActivity;
  
  protected $table = 'order_items';
  
  protected $fillable = [
    'order_type', 'order_id', 'product_id', 'taxable_price', 'purchase_price', 'sale_price', 'status', 'date', 'stock'
  ];
  
  protected $with  = [ 'product' ];
  protected $casts = [
    'created_at' => 'datetime:Y-m-d H:i:s',
    'updated_at' => 'datetime:Y-m-d H:i:s',
  ];
  
  public function order()
  {
    return $this->morphTo();
  }
  
  public function getActivitylogOptions() : LogOptions
  {
    return LogOptions::defaults()
                     ->useLogName( 'Order Item' )
                     ->logAll();
  }
  
  public function product()
  {
    return $this->belongsTo( Product::class );
  }
}
