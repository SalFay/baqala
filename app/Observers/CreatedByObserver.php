<?php

namespace App\Observers;

use Illuminate\Support\Facades\Auth;

class CreatedByObserver
{
    /**
     * Handle the "creating" event.
     */
    public function creating($model): void
    {
        if (Auth::check() && !$model->created_by_id) {
            $model->created_by_id = Auth::id();
        }
    }

    /**
     * Handle the "updating" event.
     */
    public function updating($model): void
    {
        if (Auth::check()) {
            $model->updated_by_id = Auth::id();
        }
    }
}
