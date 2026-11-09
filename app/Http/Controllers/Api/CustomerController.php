<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerRequest;
use App\Models\Account;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{
  
  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function store( CustomerRequest $request )
  {
    $request->validated();
    $data = $request->all();
    
    Customer::create( $data );
    return response()->json( [ 'status' => 'ok', 'message' => 'Customer Added' ], 200 );
  } // store
  
  /**
   * @param Request $request
   * @param Customer $customer
   * @return object
   * @throws ValidationException
   */
  public function update( CustomerRequest $request, Customer $customer ) : object
  {
    $request->validated();
    $data = $request->all();
    
    $customer->update( $data );
    
    return response()->json( [ 'status' => 'ok', 'message' => 'Customer Updated' ], 200 );
  } // update
  
  /** Statement for a date range (default this month); the table posts back for its pages. */
  public function statement( Request $request, Customer $customer, \App\Services\LedgerService $ledger )
  {
    if( $request->isMethod( 'post' ) ) {
      return response()->json( $ledger->page( 'customer', $customer->id, $request ) );
    }
    $f = $ledger->filters( $request );
    return view( 'admin.ledger.statement', [
      'kind'    => 'customer',
      'party'   => $customer,
      'name'    => trim( $customer->first_name . ' ' . $customer->last_name ),
      'f'       => $f,
      'summary' => $ledger->summary( 'customer', $customer->id, $f ),
    ] );
  }
  
  /**
   * @param Customer $customer
   * @return array|string[]
   * @throws \Exception
   */
  public function destroy( Customer $customer ) : array
  {
    $customer->delete();
    return [ 'status' => 'ok', 'message' => 'Customer Deleted' ];
  }
}
