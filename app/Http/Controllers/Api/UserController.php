<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
  
  /**
   * @param UserRequest $request
   * @return JsonResponse
   */
  public function store( UserRequest $request ) : JsonResponse
  {
    if( Auth::guest() ) {
      abort( 403, 'Unauthenticated' );
    }
    $request->validated();
    
    $data = $request->all();
    $data[ 'password' ] = Hash::make( $request[ 'password' ] );
    
    User::create( $data );
    
    return response()->json( [ 'status' => 'ok', 'message' => 'User added' ], 200 );
  }
  
  /**
   * @param Request $request
   * @param         $id
   * @return JsonResponse
   * @throws ValidationException
   */
  public function update( Request $request, $id ) : JsonResponse
  {
    
    $user = User::findOrfail( $id );
    $this->validate( $request, [
      'first_name' => [
        'required',
      ],
      'email'      => [
        'required', 'string', 'max:191', "unique:users,email,$id,id",
      ],
    ] );
    
    $data = $request->all();
    if( !empty( $request[ 'password' ] ) ) {
      $data[ 'password' ] = Hash::make( $request[ 'password' ] );
    } else {
      $data[ 'password' ] = $user->password;
    }
    //update the category
    
    $user->update( $data );
    
    return response()->json( [ 'status' => 'ok', 'message' => 'User Updated' ], 200 );
  }
  
  /**
   * @param User $user
   * @return string[]
   * @throws \Exception
   */
  public function destroy( User $user ) : array
  {
    $user->delete();
    return [ 'status' => 'ok', 'message' => 'User Deleted' ];
  }
  
  public function verify( User $user ) : JsonResponse
  {
    $user->verified = 1;
    
    $user->save();
    
    return JsonResponse::create( [ 'status' => 'ok', 'message' => 'User Verified' ] );
  }// verify
  
}
