<?php

namespace App\Models;

use App\Helpers\Ui;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Yajra\DataTables\DataTables;

class Product extends Model
{
  use HasFactory, SoftDeletes;
  use LogsActivity;
  
  protected $fillable = [
    'name', 'arabic_name', 'pid', 'purchase_price', 'sale_price', 'category_id', 'status', 'taxable', 'taxable_price'
  ];
  
  protected $casts = [
    'created_at' => 'datetime:Y-m-d H:i:s',
    'updated_at' => 'datetime:Y-m-d H:i:s',
  ];
  
  protected $with = [ 'category' ];
  
  public function category() : BelongsTo
  {
    return $this->belongsTo( Category::class );
  }
  
  public function getActivitylogOptions() : LogOptions
  {
    return LogOptions::defaults()
                     ->useLogName( 'Product' )
                     ->logAll();
  }
  
  public function getFullNameAttribute()
  {
    return "{$this->name} {$this->category->code}";
  }
  
}
