<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Bank extends Model
{
  use HasFactory, SoftDeletes;
  use LogsActivity;
  
  protected $fillable = [
    'name', 'account_number'
  ];
  
  protected $casts = [
    'created_at' => 'datetime:Y-m-d H:i:s',
    'updated_at' => 'datetime:Y-m-d H:i:s',
  ];
  
  public function getActivitylogOptions() : LogOptions
  {
    return LogOptions::defaults()
                     ->useLogName( 'Bank' )
                     ->logAll();
  }
  
}
