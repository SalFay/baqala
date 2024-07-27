<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
  public function edit( Role $role )
  {
    $output = [
      'role'        => $role->permissions ?? [],
      'permissions' => config( 'permissions' )
    ];
    return JsonResponse::create( $output );
  }
  
  public function update( Request $request, Role $role )
  {
    $role->permissions = $request->permissions;
    $role->save();
    return response()->json( [ 'success' => 'Permissions Added' ], 200 );
  }
}
