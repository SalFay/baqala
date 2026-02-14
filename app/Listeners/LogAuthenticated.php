<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Authenticated;

class LogAuthenticated
{
    /**
     * Handle the authenticated event.
     * Set redirect path based on user role (used by auth middleware).
     */
    public function handle(Authenticated $event): void
    {
        // Store redirect path in session for use by auth controller
        if (method_exists($event->user, 'redirection')) {
            session(['intended_redirect' => $event->user->redirection()]);
        }
    }
}
