<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Services\RoleService;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class RoleController extends Controller
{
  
  private $service;
  
  public function __construct( RoleService $service )
  {
    $this->service = $service;
  }
  
  /**
   * @return Application|Factory|View
   */
  public function index()
  {
    return view( 'admin.roles.index', [ 'abilities' => Role::allPermissions() ] );
  }// index
  
  /**
   * @return mixed
   * @throws Exception
   */
  public function indexAjax( Request $request, DataTables $dataTables )
  {
    return $this->service->dataTables( $request, $dataTables );
  }
  
  public function edit( $role ) : Role
  {
    return Role::findOrFail( $role );
  }// edit
  
  public function editPermissions( Role $role )
  {
    $data = [
      'role_permissions' => $role->permissions(),
      'abilities'        => $role::allPermissions()
    ];
    
    return view( 'admin.roles.permissions-table', $data );
  }// editPermissions
  
  public function updatePermissions( Request $request, Role $role ) : array
  {
    $abilities = $request->get( 'permissions' );
    if( empty( $abilities ) ) {
      $abilities = [];
    }
    $abilities = serialize( $abilities );
    $role->permissions = $abilities;
    $role->save();
    return [ 'status' => 'ok', 'message' => 'Permissions Updated' ];
  }// updatePermissions
  
}
