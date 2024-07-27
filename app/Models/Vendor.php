<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Vendor extends Model
{
  
  use HasFactory, SoftDeletes;
  use LogsActivity;
  
  protected $fillable = [
    'name',
    'mobile',
    'address',
    'status'
  ];
  
  protected $casts = [
    'created_at' => 'datetime:Y-m-d H:i:s',
    'updated_at' => 'datetime:Y-m-d H:i:s',
  ];
  
  public function paymentMethods() : MorphMany
  {
    return $this->morphMany( PaymentMethods::class, 'paymentable' );
  }
  
  public function getActivitylogOptions() : LogOptions
  {
    return LogOptions::defaults()
                     ->useLogName( 'Vendor' )
                     ->logAll();
  }
  
  public function account() : MorphMany
  {
    return $this->morphMany( Account::class, 'partyable' );
  }
  
}
