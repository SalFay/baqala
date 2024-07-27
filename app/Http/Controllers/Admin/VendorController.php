<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethods;
use App\Models\Vendor;
use App\Services\VendorService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class VendorController extends Controller
{
  private $service;
  
  public function __construct( VendorService $service )
  {
    $this->service = $service;
  }
  
  /**
   * @param Request $request
   * @return Application|Factory|View
   */
  public function index( Request $request, DataTables $dataTables )
  {
    if( $request->ajax() && $request->isMethod( 'post' ) ) {
      return $this->service->dataTables( $request, $dataTables );
    }
    return view( 'admin.vendors.index' );
  }
  
  public function payments( Vendor $vendor )
  {
    $payments = PaymentMethods::where( 'paymentable_id', $vendor->id )
                              ->where( 'paymentable_type', Vendor::class )->get();
    return view( 'admin.payments.index', [
      'payments' => $payments,
      'name'     => $vendor->name,
      'id'       => $vendor->id,
      'model'    => 'vendor'
    ] );
    
  }
  
  /**
   * @param $vendor
   * @return Vendor
   */
  public function edit( $vendor ) : Vendor
  {
    return Vendor::findOrFail( $vendor );
  }// edit
}
