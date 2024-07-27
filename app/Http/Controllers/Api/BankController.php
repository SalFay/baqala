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
  
  public function statement( Bank $bank )
  {
    $statement = Account::where( 'party_id', $bank->id )
                        ->where( 'party_type', Bank::class )->
      orWhere( 'bank_id', $bank->id )->orderBy( 'created_at', 'ASC' )->get();
    
    $bank = $bank->name;
    return view( 'admin.banks.statement', compact( 'statement', 'bank' ) );
  }
}
