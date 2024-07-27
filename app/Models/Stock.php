<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Stock extends Model
{
  use HasFactory, SoftDeletes;
  use LogsActivity;
  
  /**
   * The attributes that are mass assignable.
   * @var array
   */
  protected $fillable = [
    'vendor_id', 'payment_type', 'invoice_no', 'date', 'total', 'sub_total', 'discount', 'delivery_charges', 'credit', 'debit'
  ];
  
  protected $with = [ 'vendor', 'items' ];
  
  protected $casts = [
    'created_at' => 'datetime:Y-m-d H:i:s',
    'updated_at' => 'datetime:Y-m-d H:i:s',
  ];
  
  public function getActivitylogOptions() : LogOptions
  {
    return LogOptions::defaults()
                     ->useLogName( 'Stock' )
                     ->logAll();
  }
  
  public function vendor()
  {
    return $this->belongsTo( Vendor::class );
  }
  
  public function items()
  {
    return $this->morphMany( InventoryLog::class, 'order' );
  }
  
  public function inventory()
  {
    return $this->hasMany( Inventory::class );
  }
  
}
