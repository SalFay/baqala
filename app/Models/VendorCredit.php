<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class VendorCredit extends Model
{
  use HasFactory, SoftDeletes;
  use LogsActivity;
  
  protected $table = 'vendor_credits';
  
  protected $fillable = [
    'id', 'vendor_id', 'stock_id', 'credit', 'debit', 'total', 'comments'
  ];
  protected $with     = [ 'vendor', 'stock' ];
  
  protected $casts = [
    'created_at' => 'datetime:Y-m-d H:i:s',
    'updated_at' => 'datetime:Y-m-d H:i:s',
  ];
  
  public function vendor()
  {
    return $this->belongsTo( Vendor::class, 'vendor_id' );
  }
  
  public function getActivitylogOptions() : LogOptions
  {
    return LogOptions::defaults()
                     ->useLogName( 'Vendor Credit' )
                     ->logAll();
  }
  
  public function stock()
  {
    return $this->belongsTo( Stock::class, 'stock_id' );
  }
  
}
