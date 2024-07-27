<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CustomerCredit extends Model
{
  use HasFactory, SoftDeletes;
  use LogsActivity;
  
  protected $table = 'customer_credits';
  
  protected $fillable = [
    'id', 'customer_id', 'order_id', 'credit', 'debit', 'total', 'comments'
  ];
  protected $with     = [ 'customer', 'order' ];
  
  protected $casts = [
    'created_at' => 'datetime:Y-m-d H:i:s',
    'updated_at' => 'datetime:Y-m-d H:i:s',
  ];
  
  public function customer()
  {
    return $this->belongsTo( Customer::class, 'customer_id' );
  }
  
  public function getActivitylogOptions() : LogOptions
  {
    return LogOptions::defaults()
                     ->useLogName( 'Customer Credit' )
                     ->logAll();
  }
  
  public function order()
  {
    return $this->belongsTo( Order::class, 'order_id' );
  }
  
}
