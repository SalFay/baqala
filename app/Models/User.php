<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Lab404\Impersonate\Models\Impersonate;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Config;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class User extends Authenticatable
{
  
  use HasApiTokens;
  use HasFactory;
  use HasProfilePhoto;
  use Notifiable;
  use Impersonate;
  use TwoFactorAuthenticatable, SoftDeletes;
  use LogsActivity;
  
  /**
   * The attributes that are mass assignable.
   * @var array
   */
  protected $fillable = [
    'id', 'name', 'first_name', 'last_name', 'email',
    'password',
    'status',
    'role_id', 'phone'
  ];
  
  /**
   * The attributes that should be hidden for arrays.
   * @var array
   */
  protected $hidden = [
    'password',
    'remember_token',
    'two_factor_recovery_codes',
    'two_factor_secret',
  ];
  
  /**
   * The attributes that should be cast to native types.
   * @var array
   */
  protected $casts = [
    'email_verified_at' => 'datetime',
    'created_at'        => 'datetime:Y-m-d H:i:s',
    'updated_at'        => 'datetime:Y-m-d H:i:s',
  ];
  
  /**
   * The accessors to append to the model's array form.
   * @var array
   */
  protected $appends = [
    'profile_photo_url',
  ];
  
  protected $with = [ 'role' ];
  
  /**
   * User permissions holder
   * @var array User permissions
   */
  protected $permissions = [];
  
  /**
   * @var bool $isAdmin
   */
  protected $isAdmin;
  
  /**
   * @return mixed
   */
  public function getJWTIdentifier()
  {
    return $this->getKey();
  }
  
  /**
   * Return a key value array, containing any custom claims to be added to the JWT.
   * @return array
   */
  public function getJWTCustomClaims()
  {
    return [];
  }
  
  public function role()
  {
    return $this->belongsTo( Role::class );
  }
  
  public function getFullNameAttribute()
  {
    return "{$this->first_name} {$this->last_name}";
  }
  
  public function getFirstLetterAttribute()
  {
    return substr( $this->first_name, 0, 1 ) . substr( $this->last_name, 0, 1 );
  }
  
  /**
   * @param $ability
   * @return bool
   */
  public function hasPermissionTo(
    $ability
  ) : bool {
    if( empty( $this->permissions ) ) {
      $role = $this->role;
      $role_permissions = $role->permissions();
      $this->permissions = array_merge( $this->permissions, $role_permissions );
    }
    
    return in_array( $ability, $this->permissions, true );
  }
  
  public function isAdmin() : bool
  {
    if( $this->isAdmin === null ) {
      $this->isAdmin = false;
      $adminRole = (int) option( 'role.admin', 0 );
      if( empty( $adminRole ) ) {
        return $this->isAdmin;
      }
      $role = (int) $this->role->id;
      if( $role === $adminRole ) {
        $this->isAdmin = true;
      }
    }
    
    return $this->isAdmin;
  }
  
  public
  function isRootUser() : bool
  {
    $rootUsers = Config::get( 'app.root_users' );
    
    return in_array( $this->email, $rootUsers, true );
  }
  
  public
  function isManager() : bool
  {
    if( $this->isManager === null ) {
      $this->isManager = false;
      $managerRole = (int) option( 'role.manager', 0 );
      if( empty( $managerRole ) ) {
        return $this->isManager;
      }
      $role = (int) $this->role->id;
      if( $role === $managerRole ) {
        $this->isManager = true;
      }
    }
    
    return $this->isManager;
  }
  
  public
  function redirection()
  {
    $primary_role = $this->role->id ?? '';
    
    return option( 'redirect.role.' . $primary_role, url( 'admin' ) );
    
  }// redirection
  
  public function getActivitylogOptions() : LogOptions
  {
    return LogOptions::defaults()
                     ->useLogName( 'User' )
                     ->logAll();
  }
  
}// User
