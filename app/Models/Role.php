<?php

namespace App\Models;

use Config;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\Role
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property int $sort_order
 * @property string $permissions
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property string $screen
 * @property-read Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @method static Builder|Role findSimilarSlugs( $attribute, $config, $slug )
 * @method static Builder|Role newModelQuery()
 * @method static Builder|Role newQuery()
 * @method static Builder|Role query()
 * @method static Builder|Role whereCreatedAt( $value )
 * @method static Builder|Role whereDeletedAt( $value )
 * @method static Builder|Role whereDescription( $value )
 * @method static Builder|Role whereId( $value )
 * @method static Builder|Role whereName( $value )
 * @method static Builder|Role wherePermissions( $value )
 * @method static Builder|Role whereScreen( $value )
 * @method static Builder|Role whereSlug( $value )
 * @method static Builder|Role whereSortOrder( $value )
 * @method static Builder|Role whereUpdatedAt( $value )
 * @mixin Eloquent
 */
class Role extends Model
{
  
  use HasFactory, SoftDeletes;
  
  use LogsActivity;
  
  /**
   * The attributes that are mass assignable.
   * @var array
   */
  protected $fillable = [
    'name', 'description', 'slug', 'permissions'
  ];
  
  protected $casts = [
    'created_at'  => 'datetime:Y-m-d H:i:s',
    'updated_at'  => 'datetime:Y-m-d H:i:s',
    'permissions' => 'array'
  ];
  
  public function user()
  {
    return $this->hasMany( User::class );
  }
  
  public function getActivitylogOptions() : LogOptions
  {
    return LogOptions::defaults()
                     ->useLogName( 'Role' )
                     ->logAll();
  }
  
  /**
   * Get All Permissions
   * @return array
   */
  public static function allPermissions() : array
  {
    $output = [];
    $files = Config::get( 'permissions.permission_files' );
    
    foreach( $files as $file ) {
      $output[ $file ] = require app_path( 'Permissions/' . $file . '.php' );
    }
    return $output;
  }// allPermissions
  
  /**
   * Role Associated Permissions
   * @return mixed
   */
  public function permissions()
  {
    return unserialize( $this->permissions, [ 'allowed_classes' => false ] );
  }
  
  /*public function setPermissionsAttribute( $permissions = null )
  {
      if( $permissions === null ) {
          $permissions = [];
      }
      return serialize( $permissions );
  }*/
  
}
