<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdminMiddleware
{
  /**
   * Handle an incoming request.
   * @param Request $request
   * @param Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
   * @return Response|RedirectResponse
   */
  public function handle( Request $request, Closure $next )
  {
    if( !\Auth::check() ) {
      abort( 404 );
    }
    return $next( $request );
  }
}
