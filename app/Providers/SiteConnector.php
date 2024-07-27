<?php

namespace App\Providers;

use App\Models\Site;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class SiteConnector extends ServiceProvider
{
  /**
   * Register services.
   * @return void
   */
  public function register()
  {
    //
  }
  
  /**
   * Bootstrap services.
   * @return void
   */
  public function boot()
  {
    $host = request()->getHost();
    $site = Site::where( 'domain', $host )->firstOrFail();
    Config::set( 'database.connections.site_db.database', $site->db_name );
    Config::get( 'database.default', 'site_db' );
    DB::setDefaultConnection( 'site_db' );
  }
}
