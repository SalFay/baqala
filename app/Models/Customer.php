<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Customer extends Model
{
  
  use Notifiable, SoftDeletes;
  use LogsActivity;
  
  protected $fillable = [
    'first_name',
    'last_name',
    'business_name',
    'billing_address',
    'billing_city',
    'billing_state',
    'billing_zipcode',
    'billing_country_id',
    'shipping_address',
    'shipping_city',
    'shipping_state',
    'shipping_zipcode',
    'shipping_country_id',
    'phone_home',
    'phone_work',
    'phone_mobile',
    'phone_other',
    'email',
    'address',
    'status'
  ];
  
  public function getActivitylogOptions() : LogOptions
  {
    return LogOptions::defaults()
                     ->useLogName( 'Customer' )
                     ->logAll();
  }
  
  protected $casts = [
    'created_at' => 'datetime:Y-m-d H:i:s',
    'updated_at' => 'datetime:Y-m-d H:i:s',
  ];
  
  public function paymentMethods() : MorphMany
  {
    return $this->morphMany( PaymentMethods::class, 'paymentable' );
  }
  
  public function stock() : HasMany
  {
    return $this->hasMany( Stock::class );
  }
  
  public function inventory() : HasMany
  {
    return $this->hasMany( Inventory::class );
  }
  
  public function getFullNameAttribute()
  {
    return "{$this->first_name} {$this->last_name}";
  }
  
  public function account() : MorphMany
  {
    return $this->morphMany( Account::class, 'partyable' );
  }
  
}
