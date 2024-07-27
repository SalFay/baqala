<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CustomerCreditController extends Controller
{
  
  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function store( Request $request )
  {
    $this->validate( $request, [
      'customer_id' => 'required'
    ] );
    Account::create( $request->all() );
    return response()->json( [ 'status' => 'ok', 'message' => 'Credit Book Added' ], 200 );
  } // store
  
  /**
   * @param Request $request
   * @param Account $credit
   * @return object
   */
  public function update( Request $request, Account $credit ) : object
  {
    $credit->update( $request->all() );
    
    return response()->json( [ 'status' => 'ok', 'message' => 'Credit Book Updated' ], 200 );
  } // update
  
  /**
   * @param Account $credit
   * @return array|string[]
   * @throws \Exception
   */
  public function destroy( Account $credit ) : array
  {
    $credit->delete();
    return [ 'status' => 'ok', 'message' => 'Credit Book Deleted' ];
  }
}
