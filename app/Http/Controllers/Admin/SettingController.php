<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Option;
use Redirect;
use Spatie\Backup\BackupDestination\BackupDestination;

/**
 * Class SettingController
 * @package App\Http\Controllers\Admin
 */
class SettingController extends Controller
{
  
  /**
   * @return Factory|View
   */
  public function index()
  {
    return view( 'admin.settings.index' );
  }
  
  /**
   * @return Factory|View
   */
  public function backups()
  {
    $path = $this->backupDirectory();
    $files = File::isDirectory( $path )
      ? collect( File::files( $path ) )->sortByDesc( fn( $f ) => $f->getMTime() )->map( fn( $f ) => pathinfo( $f ) )->values()->all()
      : [];

    return view( 'admin.settings.backup', compact( 'files' ) );
  }

  public function runBackup() : array
  {
    $exitCode = Artisan::call( 'backup:run', [ '--only-db' => true, '--disable-notifications' => true ] );

    if( $exitCode !== 0 ) {
      abort( 500, 'Backup failed: ' . trim( Artisan::output() ) );
    }

    return [ 'status' => 'ok', 'message' => 'Database backup created' ];
  }

  public function download( $file )
  {
    return response()->download( $this->backupFile( $file ) );
  }

  public function delete( $file )
  {
    File::delete( $this->backupFile( $file ) );
    return Redirect::back();
  }

  /**
   * Folder spatie/laravel-backup writes to. Ask the package for the name: it turns
   * anything but letters, digits and dots into '-' ("Sherin Huts Pos" -> "Sherin-Huts-Pos").
   */
  private function backupDirectory() : string
  {
    $name = BackupDestination::create( 'local', config( 'backup.backup.name' ) )->backupName();

    return storage_path( 'app/' . $name );
  }

  /**
   * Resolve a backup file name to its path, refusing anything outside the backup folder.
   */
  private function backupFile( string $file ) : string
  {
    $path = $this->backupDirectory() . DIRECTORY_SEPARATOR . basename( $file );
    abort_unless( File::isFile( $path ), 404 );

    return $path;
  }
  
  /**
   * @return Application|Factory|View
   */
  public function rolesIndex()
  {
    $roles = Role::all();
    $currentRoles = [
      'admin'   => option( 'role.admin' ),
      'manager' => option( 'role.manager' )
    ];
    return view( 'admin.settings.roles', [ 'roles' => $roles, 'oldRole' => $currentRoles ] );
  }
  
  /**
   * @param Request $request
   * @return array|string[]
   */
  public function rolesUpdate( Request $request ) : array
  {
    
    Option::set( 'role.admin', $request->adminRole );
    Option::set( 'role.manager', $request->managerRole );
    
    return [ 'status' => 'ok', 'message' => 'Settings Updated' ];
  }
  
  /**
   * @return Application|Factory|View
   */
  public function redirectionIndex()
  {
    $roles = Role::all();
    
    $current = [];
    
    foreach( $roles as $role ) {
      $current[ $role->id ] = option( 'redirect.role.' . $role->id );
    }
    
    return view( 'admin.settings.redirects', [ 'roles' => $roles, 'current' => $current ] );
    
  }
  
  public function generalIndex()
  {
    $data = [
      'title'      => option( 'title' ),
      'mobile'     => option( 'mobile' ),
      'vat_amount' => option( 'vat_amount' ),
      'vat_number' => option( 'vat_number' ),
      'address'    => option( 'address' ),
      'logo'       => option( 'logo' ),
      'printer'    => option( 'printer' )
    
    ];
    return view( 'admin.settings.general', [ 'data' => $data ] );
    
  }
  
  public function generalUpdate( Request $request ) : array
  {
    $request->validate( [ 'file' => 'nullable|image|mimes:png,jpg,jpeg,gif,webp|max:2048' ] );

    Option::set( 'title', $request->title );
    Option::set( 'address', $request->address );
    Option::set( 'mobile', $request->mobile );
    Option::set( 'vat_amount', $request->vat_amount );
    Option::set( 'vat_number', $request->vat_number );
    Option::set( 'printer', $request->printer );
    if( $request->hasFile( 'file' ) ) {
      $destinationDir = public_path( 'assets' );
      $filename = $request->file->hashName();
      $request->file->move( $destinationDir, $filename );
      Option::set( 'logo', $filename );
    }
    
    return [ 'status' => 'ok', 'message' => 'Settings Updated' ];
  }
  
  /**
   * @param Request $request
   * @return array|string[]
   */
  public function redirectionUpdate( Request $request ) : array
  {
    $redirect = $request->redirect;
    
    foreach( $redirect as $role => $location ) {
      if( empty( $location ) ) {
        $location = 'front';
      }
      Option::set( 'redirect.role.' . $role, $location );
    }
    
    return [ 'status' => 'ok', 'message' => 'Settings Updated' ];
  }
  
}// SettingController
