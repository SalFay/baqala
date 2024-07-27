<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use App\Repositories\CompanyRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use phpDocumentor\Reflection\Types\Null_;

/**
 * Class AuthController
 * @package App\Http\Controllers\Api
 */
class AuthController extends Controller
{
  private $companyRepository;
  
  /**
   * Create a new AuthController instance.
   * @return void
   */
  public function __construct( CompanyRepository $companyRepository )
  {
    $this->middleware( 'jwt', [ 'except' => [ 'login' ] ] );
    $this->companyRepository = $companyRepository;
  } // __construct
  
  /**
   * Get a JWT via given credentials.
   * @param Request $request
   * @return JsonResponse
   */
  public function login( Request $request ) : JsonResponse
  {
    $credentials = $request->only( [ 'email', 'password' ] );
    $user = User::where( 'email', $credentials[ 'email' ] )
                ->first();
    if( empty( $user->id ) ) {
      auth_logs( $credentials[ 'email' ], 'Not Found' );
      return response()->json( [
        'error' => 'User Not Found'
      ], 401 );
    }
    
    if( $user->verified === 0 ) {
      auth_logs( $credentials[ 'email' ], 'First Time Login' );
      return response()->json( [ 'error' => 'Change Your Password', 'code' => 'first_time', 'data' => $user ],
        401 );
    }
    
    if( $user->status === 'Suspended' || $user->status
                                         === null ) {
      auth_logs( $credentials[ 'email' ], 'Suspended' );
      return response()->json( [ 'error' => 'Access Denied' ], 401 );
    }
    
    if( !$token = auth()->attempt( $credentials ) ) {
      auth_logs( $request[ 'email' ], 'Failed' );
      return response()->json( [ 'error' => 'Unauthorized' ], 401 );
    }
    auth_logs( $request[ 'email' ], 'Pass' );
    
    return $this->respondWithToken( $token );
    
  }// login
  
  /**
   * Get the authenticated User.
   * @return JsonResponse
   */
  public function me() : JsonResponse
  {
    $user = auth()->user();
    $user->load( [ 'companies', 'role' ] );
    return response()->json( $user );
  }
  
  /**
   * Log the user out (Invalidate the token).
   * @return JsonResponse
   */
  public function logout() : JsonResponse
  {
    Auth::guard( 'api' )->logout();
    return response()->json( [ 'message' => 'Successfully logged out' ] );
  }
  
  /**
   * Refresh a token.
   * @return JsonResponse
   */
  public function refresh() : JsonResponse
  {
    return $this->respondWithToken( auth()->refresh() );
  }
  
  /**
   * Get the token array structure.
   * @param string $token
   * @return JsonResponse
   */
  protected function respondWithToken( $token ) : JsonResponse
  {
    $user = auth()->user();
    $user->load( [ 'role' ] );
    unset( $user->companies );
    $user->companies = $this->companyRepository->getAllByRole( [] );
    $user->api_token = $token;
    $user->access_token = $token;
    $user->token_type = 'bearer';
    $user->expires_in = auth()->factory()->getTTL() * 60;
    return response()->json( $user );
  }
} // AuthController
