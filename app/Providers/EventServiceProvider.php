<?php

namespace App\Providers;

use App\Listeners\LogAuthenticated;
use App\Listeners\UserLoginEvent;
use App\Listeners\UserLogoutEvent;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
  /**
   * The event listener mappings for the application.
   * @var array
   */
  protected $listen = [
    Registered::class    => [
      SendEmailVerificationNotification::class,
    ],
    Authenticated::class => [
      LogAuthenticated::class
    ],
    Login::class         => [
      UserLoginEvent::class,
    ],
    Logout::class        => [
      UserLogoutEvent::class,
    ],
  ];
  
  /**
   * Register any events for your application.
   * @return void
   */
  public function boot()
  {
    //
  }
}
