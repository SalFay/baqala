<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
  /**
   * @param Request $request
   * @param Closure $next
   * @param ...$guards
   * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|mixed
   * @throws \Psr\Container\ContainerExceptionInterface
   * @throws \Psr\Container\NotFoundExceptionInterface
   */
  public function handle( Request $request, Closure $next, ...$guards )
  {
    $guards = empty( $guards ) ? [ null ] : $guards;
    $redirectTo = RouteServiceProvider::HOME;
    foreach( $guards as $guard ) {
      if( Auth::guard( $guard )->check() ) {
        //in case intended url is available
        if( session()->has( 'url.intended' ) ) {
          $redirectTo = session()->get( 'url.intended' );
          session()->forget( 'url.intended' );
        }
        
        $request->session()->regenerate();
        
        return redirect( $redirectTo );
      }
    }
    
    return $next( $request );
  }
}
