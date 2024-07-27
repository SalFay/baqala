<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Bank;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AccountController extends Controller
{
  
  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function store( Request $request )
  {
    $data = $request->all();
    $data[ 'bank' ] = addBank( $request->bank );
    
    if( $request->status === 'Customer' ) {
      Account::create( [
        'party_type' => Customer::class,
        'party_id'   => $request->customer_id,
        'credit'     => $request->credit,
        'debit'      => $request->debit,
        'discount'   => $request->discount,
        'comments'   => $request->comments,
        'bank_id'    => $data[ 'bank' ],
        'cheque'     => $request->cheque,
      ] );
    }
    if( $request->status === 'Vendor' ) {
      Account::create( [
        'party_type' => Vendor::class,
        'party_id'   => $request->vendor_id,
        'debit'      => $request->debit + $request->discount,
        'credit'     => $request->credit,
        'discount'   => $request->discount,
        'comments'   => $request->comments,
        'bank_id'    => $data[ 'bank' ],
        'cheque'     => $request->cheque,
      ] );
    }
    return response()->json( [ 'status' => 'ok', 'message' => 'Payment Added' ], 200 );
  } // store
  
  public function expenses( Request $request )
  {
    $data = $request->all();
    $data[ 'bank' ] = addBank( $request->bank );
    $data[ 'expense' ] = addExpense( $request->expense );
    
    Account::create( [
      'party_type' => Expense::class,
      'party_id'   => $data[ 'expense' ],
      'credit'     => 0,
      'debit'      => $request->debit,
      'comments'   => $request->comments,
      'bank_id'    => $data[ 'bank' ],
      'cheque'     => $request->cheque,
    ] );
    return response()->json( [ 'status' => 'ok', 'message' => 'Expense Added' ], 200 );
  } // Expense
  
  public function cash( Request $request )
  {
    $data = $request->all();
    $data[ 'bank' ] = addBank( $request->bank );
    
    Account::create( [
      'party_type' => Bank::class,
      'party_id'   => $data[ 'bank' ],
      'credit'     => $request->credit,
      'debit'      => $request->debit,
      'comments'   => $request->comments,
      'bank_id'    => $data[ 'bank' ],
      'cheque'     => $request->cheque,
    ] );
    return response()->json( [ 'status' => 'ok', 'message' => 'Cash Added' ], 200 );
  } // Expense
  
  public function transfer( Request $request )
  {
    $data = $request->all();
    
    if( $request->from_bank == 0 ) {
      Account::create( [
        'party_type' => 'Cash',
        'party_id'   => 0,
        'debit'      => $request->credit,
        'bank_id'    => $data[ 'from_bank' ],
      ] );
      Account::create( [
        'party_type' => Bank::class,
        'party_id'   => $data[ 'to_bank' ],
        'credit'     => $request->credit,
        'comments'   => $request->comments,
        'bank_id'    => $data[ 'to_bank' ],
      ] );
    }
    if( $request->to_bank == 0 ) {
      Account::create( [
        'party_type' => Bank::class,
        'party_id'   => $data[ 'from_bank' ],
        'debit'      => $request->credit,
        'bank_id'    => $data[ 'from_bank' ],
      ] );
      Account::create( [
        'party_type' => 'Cash',
        'party_id'   => 0,
        'credit'     => $request->credit,
        'comments'   => $request->comments,
        'bank_id'    => $data[ 'to_bank' ],
      ] );
    }
    if( $request->from_bank != 0 && $request->to_bank != 0 ) {
      Account::create( [
        'party_type' => Bank::class,
        'party_id'   => $data[ 'from_bank' ],
        'debit'      => $request->credit,
        'bank_id'    => $data[ 'from_bank' ],
      ] );
      
      Account::create( [
        'party_type' => Bank::class,
        'party_id'   => $data[ 'to_bank' ],
        'credit'     => $request->credit,
        'comments'   => $request->comments,
        'bank_id'    => $data[ 'to_bank' ],
      ] );
      
    }
    return response()->json( [ 'status' => 'ok', 'message' => 'Transfer Completed' ], 200 );
  } // Expense
  
  /**
   * @param Request $request
   * @param Account $account
   * @return object
   */
  public function update( Request $request, Account $account ) : object
  {
    
    $data = $request->all();
    $data[ 'bank_id' ] = addBank( $request->bank );
    
    $account->update( $data );
    
    return response()->json( [ 'status' => 'ok', 'message' => 'Category Updated' ], 200 );
  } // update
  
  /**
   * @param Account $account
   * @return array|string[]
   * @throws \Exception
   */
  public function destroy( Account $account ) : array
  {
    $account->delete();
    return [ 'status' => 'ok', 'message' => 'Category Deleted' ];
  }
}
