<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Opcodes\LogViewer\Facades\LogViewer;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   * @return void
   */
  public function register()
  {
    //
  }

  /**
   * Bootstrap any application services.
   * @return void
   */
  public function boot()
  {
    // /log-viewer: same permission as backups. Without this callback the package
    // only checks auth when APP_ENV=production, so logs would be public otherwise.
    LogViewer::auth( fn( $request ) => $request->user() && user_can( 'access settings' ) );

    // Read-only: logs are never deleted from the browser.
    Gate::define( 'deleteLogFile', fn() => false );
    Gate::define( 'deleteLogFolder', fn() => false );
  }
}
