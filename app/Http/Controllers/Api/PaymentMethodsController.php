<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\PaymentMethods;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentMethodsController extends Controller
{
  
  /**
   * @param Request $request
   * @return JsonResponse
   */
  public function store( Request $request )
  {
    $data = $request->all();
    if( $request->model === 'customer' ) {
      $customer = Customer::findOrFail( $request->id );
      if( $request->source === 'Cash' ) {
        
        $customer->paymentMethods()->create( [ 'source' => $request->source ] );
      } else {
        $customer->paymentMethods()->create( [
          'source'         => $request->source,
          'account_title'  => $request->account_title,
          'account_number' => $request->account_number,
          'account_branch' => $request->account_branch,
          'name'           => $request->name
        ] );
      }
      
    } elseif( $request->model === 'vendor' ) {
      $vendor = Vendor::findOrFail( $request->id );
      if( $request->source === 'Cash' ) {
        
        $vendor->paymentMethods()->create( [ 'source' => $request->source ] );
      } else {
        $vendor->paymentMethods()->create( [
          'source'         => $request->source,
          'account_title'  => $request->account_title,
          'account_number' => $request->account_number,
          'account_branch' => $request->account_branch,
          'name'           => $request->name
        ] );
      }
    }
    
    return response()->json( [ 'status' => 'ok', 'message' => 'Payment Method Added' ], 200 );
  } // store
  
  public function edit( $payment ) : PaymentMethods
  {
    return PaymentMethods::findOrFail( $payment );
  }// edit
  
  public function update( Request $request, $id ) : JsonResponse
  {
    
    $payment = PaymentMethods::findOrfail( $id );
    
    $payment->update( [
      'source'         => $request->source,
      'account_title'  => $request->account_title,
      'account_number' => $request->account_number,
      'account_branch' => $request->account_branch,
      'name'           => $request->name
    ] );
    
    return response()->json( [ 'status' => 'ok', 'message' => 'Payment Method Updated' ], 200 );
  }
  
  /**
   * @param PaymentMethods $payment
   * @return array|string[]
   * @throws \Exception
   */
  public function destroy( PaymentMethods $payment ) : array
  {
    $payment->delete();
    return [ 'status' => 'ok', 'message' => 'Payment Method Deleted' ];
  }
}
