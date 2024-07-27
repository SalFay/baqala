<?php

namespace App\Models;

use App\Helpers\Ui;
use Illuminate\Database\Eloquent\Model;
use Yajra\DataTables\DataTables;

class AuthLog extends Model
{
  protected $fillable = [
    'email', 'ip_address', 'authenticated', 'logged_in'
  ];
  
  protected $casts = [
    'created_at' => 'datetime:m-d-Y H:i:s',
    'updated_at' => 'datetime:m-d-Y H:i:s',
    'logged_in'  => 'datetime:m-d-Y H:i:s',
  ];
  
  public static function dataTablesAjax()
  {
    $table = DataTables::of( self::query() );
    return $table->toJson();
  }
  
}
