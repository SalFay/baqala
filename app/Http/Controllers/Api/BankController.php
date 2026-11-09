<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Bank;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BankController extends Controller
{
  
  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function store( Request $request )
  {
    $this->validate( $request, [
      'account_number' => 'required|string|max:191|unique:banks',
      'name'           => 'required',
    
    ] );
    Bank::create( $request->all() );
    return response()->json( [ 'status' => 'ok', 'message' => 'Bank Added' ], 200 );
  } // store
  
  /**
   * @param Request $request
   * @param Bank $bank
   * @return object
   * @throws ValidationException
   */
  public function update( Request $request, Bank $bank ) : object
  {
    $this->validate( $request, [
      'account_number' => [
        'required', 'string', 'max:191',
        Rule::unique( 'banks' )->ignore( $bank->id ),
      ],
    ] );
    //update the Bank
    
    $bank->update( $request->all() );
    
    return response()->json( [ 'status' => 'ok', 'message' => 'Bank Updated' ], 200 );
  } // update
  
  /**
   * @param Bank $bank
   * @return array|string[]
   * @throws \Exception
   */
  public function destroy( Bank $bank ) : array
  {
    $bank->delete();
    return [ 'status' => 'ok', 'message' => 'Bank Deleted' ];
  }
  
  /** Statement for a date range (default this month); the table posts back for its pages. */
  public function statement( \Illuminate\Http\Request $request, Bank $bank, \App\Services\LedgerService $ledger )
  {
    if( $request->isMethod( 'post' ) ) {
      return response()->json( $ledger->page( 'bank', $bank->id, $request ) );
    }
    $f = $ledger->filters( $request );
    return view( 'admin.ledger.statement', [
      'kind'    => 'bank',
      'party'   => $bank,
      'name'    => $bank->name,
      'f'       => $f,
      'summary' => $ledger->summary( 'bank', $bank->id, $f ),
    ] );
  }
}
