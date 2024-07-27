<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Class CheckPermission
 * @package App\Http\Middleware
 */
class CheckPermission
{
  /**
   * Handle an incoming request.
   * @param Request $request
   * @param Closure $next
   * @param array $permissions
   * @return mixed|void
   */
  public function handle( $request, Closure $next, ...$permissions )
  {
    //dd($permissions);
    /* --------------------------------------------------------------
     *  Check User permission and root User
     * --------------------------------------------------------------
     */
    foreach( $permissions as $permission ) {
      if( user_can( $permission ) ) {
        /* --------------------------------------------------------------
         *  Permission Passed
         * --------------------------------------------------------------
         */
        return $next( $request );
      }
    }
    
    /* --------------------------------------------------------------
     *  Permission denied
     * --------------------------------------------------------------
     */
    abort( 403 );
  }
}
