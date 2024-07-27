<?php

use App\Models\AuthLog;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

/**
 * @return Authenticatable|null|User
 */
function user()
{
  return Auth::user();
}

function auth_logs( $email, $auth )
{
  AuthLog::create( [
    'email'         => $email,
    'authenticated' => $auth,
    'ip_address'    => \Request::ip(),
    'logged_in'     => date( 'Y-m-d H:i:s' )
  ] );
}

/**
 * @return bool
 */
function isAdmin()
{
  return \user()->isAdmin();
}

function user_can( $ability, $options = [] )
{
  /*if( !isset( $options[ 'check.admin' ] ) ) {
      $options[ 'check.admin' ] = true;
  }

  if( !isset( $options[ 'check.root' ] ) ) {
      $options[ 'check.root' ] = true;
  }
  */
  /* --------------------------------------------------------------
   *  Check if Current user is Root User
   * --------------------------------------------------------------
   */
  /*if( ( $options[ 'check.root' ] === true ) && user()->isRootUser() ) {
      return true;
  }*/
  
  // If Current User has Admin Role
  /*if( ( $options[ 'check.admin' ] === true ) && Auth::user()->isAdmin() === true ) {
      return true;
  }*/
  
  /**
   * If the user has the ability
   * @see \App\User::can()
   */
  return Auth::user()->can( $ability, $options );
}
