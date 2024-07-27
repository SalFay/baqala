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
  
}
