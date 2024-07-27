<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Class RoleController
 * @package App\Http\Controllers\Api
 */
class RoleController extends Controller
{
  
  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function store( Request $request )
  {
    $this->validate( $request, [
      'name' => 'required|string|max:191|unique:roles',
    
    ] );
    $role = new Role();
    $role->name = $request->name;
    $role->slug = str_slug( $request->name );
    
    $role->description = empty( $request->description ) ? '' : $request->description;
    
    $role->save();
    return response()->json( [ 'status' => 'ok', 'message' => 'Role Updated' ], 200 );
  } // store
  
  /**
   * Update the specified resource in storage.
   * @param Request $request
   * @param Role $role
   * @return array
   * @throws ValidationException
   */
  public function update( Request $request, Role $role ) : object
  {
    $this->validate( $request, [
      'name' => [
        'required', 'string', 'max:191',
        Rule::unique( 'roles' )->ignore( $role->id ),
      ],
    ] );
    //update the Role
    
    $role->update( $request->all() );
    
    return response()->json( [ 'status' => 'ok', 'message' => 'Role Updated' ], 200 );
  } // update
  
  public function destroy( Role $role ) : array
  {
    $role->delete();
    return [ 'status' => 'ok', 'message' => 'Role Deleted' ];
  }
}
