<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VendorCredit;
use App\Services\VendorCreditService;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class VendorCreditController extends Controller
{
  private $service;
  
  public function __construct( VendorCreditService $service )
  {
    $this->service = $service;
  }
  
  /**
   * @param Request $request
   * @param DataTables $dataTables
   * @return Application|Factory|View|JsonResponse
   * @throws Exception
   */
  public function index( Request $request, DataTables $dataTables )
  {
    if( $request->ajax() && $request->isMethod( 'post' ) ) {
      return $this->service->dataTables( $request, $dataTables );
    }
    return view( 'admin.vendorCreditBook.index' );
  }// index
  
  /**
   * @param $credit
   * @return VendorCredit
   */
  public function edit( $credit ) : VendorCredit
  {
    return VendorCredit::findOrFail( $credit );
  }// edit
  
}
