<?php

namespace App\Providers;

use App\Models\Role;
use App\Models\User;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
  /**
   * The policy mappings for the application.
   * @var array
   */
  protected $policies = [
    
    User::class => UserPolicy::class,
    Role::class => RolePolicy::class,
  ];
  
  /**
   * Register any authentication / authorization services.
   * @return void
   */
  public function boot()
  {
    $this->registerPolicies();
    
    Gate::before( function( User $user, $ability ) {
      if( $user->isRootUser() ) {
        return true;
      }
      return $user->hasPermissionTo( $ability );
    } );
  }
}
