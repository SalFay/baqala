<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerCredit;
use App\Models\VendorCredit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VendorCreditController extends Controller
{
  
  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function store( Request $request )
  {
    $this->validate( $request, [
      'vendor_id' => 'required'
    ] );
    VendorCredit::create( $request->all() );
    return response()->json( [ 'status' => 'ok', 'message' => 'Credit Book Added' ], 200 );
  } // store
  
  /**
   * @param Request $request
   * @param CustomerCredit $credit
   * @return object
   */
  public function update( Request $request, VendorCredit $credit ) : object
  {
    $credit->update( $request->all() );
    
    return response()->json( [ 'status' => 'ok', 'message' => 'Credit Book Updated' ], 200 );
  } // update
  
  /**
   * @param VendorCredit $credit
   * @return array|string[]
   * @throws \Exception
   */
  public function destroy( VendorCredit $credit ) : array
  {
    $credit->delete();
    return [ 'status' => 'ok', 'message' => 'Credit Book Deleted' ];
  }
}
