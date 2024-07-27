<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;

class UserLogoutEvent
{
  /**
   * Create the event listener.
   * @return void
   */
  public function __construct()
  {
    //
  }
  
  /**
   * Handle the event.
   * @param object $event
   * @return void
   */
  public function handle( $event )
  {
    activity()
      ->causedBy( Auth::user() )
      ->performedOn( $event->user )
      ->useLog( 'authentication' )
      ->log( 'Logged-out' );
  }
}
