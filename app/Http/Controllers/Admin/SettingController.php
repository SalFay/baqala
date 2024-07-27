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
    $files = [];
    $path = storage_path( 'app/POS' );
    $filesInFolder = File::files( $path );
    
    foreach( $filesInFolder as $path ) {
      $files[] = pathinfo( $path );
    }
    
    return view( 'admin.settings.backup', compact( 'files' ) );
  }
  
  public function download( $file )
  {
    $file_path = storage_path( 'app/POS/' . $file );
    return response()->download( $file_path );
  }
  
  public function delete( $file )
  {
    $file_path = storage_path( 'app/POS/' . $file );
    unlink( $file_path );
    return \Illuminate\Support\Facades\Redirect::back();
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
    
    Option::set( 'title', $request->title );
    Option::set( 'address', $request->address );
    Option::set( 'mobile', $request->mobile );
    Option::set( 'vat_amount', $request->vat_amount );
    Option::set( 'vat_number', $request->vat_number );
    Option::set( 'printer', $request->printer );
    if( $request->hasFile( 'file' ) ) {
      $destinationDir = public_path( 'assets' );
      $filename = $request->file->getClientOriginalName();
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
