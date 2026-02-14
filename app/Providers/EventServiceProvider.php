<?php

namespace App\Providers;

use App\Listeners\LogAuthenticated;
use App\Listeners\UserLoginEvent;
use App\Listeners\UserLogoutEvent;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\PurchaseOrder;
use App\Models\StockTransfer;
use App\Observers\CreatedByObserver;
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
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        Authenticated::class => [
            LogAuthenticated::class
        ],
        Login::class => [
            UserLoginEvent::class,
        ],
        Logout::class => [
            UserLogoutEvent::class,
        ],
    ];

    /**
     * Register any events for your application.
     * @return void
     */
    public function boot()
    {
        // Register CreatedByObserver for models that track created_by and updated_by
        Order::observe(CreatedByObserver::class);
        PurchaseOrder::observe(CreatedByObserver::class);
        StockTransfer::observe(CreatedByObserver::class);
        OrderReturn::observe(CreatedByObserver::class);
    }
}
