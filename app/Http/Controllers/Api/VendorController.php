<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class VendorController extends Controller
{
  
  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function store( Request $request )
  {
    $this->validate( $request, [
      'name' => 'required|string|max:191|unique:vendors',
    ] );
    Vendor::create( $request->all() );
    return response()->json( [ 'status' => 'ok', 'message' => 'Vendor Added' ], 200 );
  } // store
  
  public function statement( Vendor $vendor )
  {
    $statement = Account::where( 'party_id', $vendor->id )
                        ->where( 'party_type', Vendor::class )->orderBy( 'created_at', 'ASC' )->get();
    
    $vendor_name = $vendor->name;
    return view( 'admin.vendors.statement', compact( 'statement', 'vendor_name' ) );
  }
  
  /**
   * @param Request $request
   * @param Vendor $vendor
   * @return object
   * @throws ValidationException
   */
  public function update( Request $request, Vendor $vendor ) : object
  {
    $this->validate( $request, [
      'name' => [
        'required', 'string', 'max:191',
        Rule::unique( 'vendors' )->ignore( $vendor->id ),
      ],
    ] );
    //update the Vendor
    
    $vendor->update( $request->all() );
    
    return response()->json( [ 'status' => 'ok', 'message' => 'Vendor Updated' ], 200 );
  } // update
  
  /**
   * @param Vendor $vendor
   * @return array|string[]
   * @throws \Exception
   */
  public function destroy( Vendor $vendor ) : array
  {
    $vendor->delete();
    return [ 'status' => 'ok', 'message' => 'Vendor Deleted' ];
  }
}
