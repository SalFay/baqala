<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Spatie\Activitylog\ActivityLogStatus;

class UserLoginEvent
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
      ->causedByAnonymous()
      ->performedOn( $event->user )
      ->useLog( 'authentication' )
      ->log( 'Logged-in' );
  }
}
