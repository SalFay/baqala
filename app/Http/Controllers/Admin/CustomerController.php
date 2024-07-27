<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

/**
 * Class CustomerController
 * @package App\Http\Controllers\Admin
 */
class CustomerController extends Controller
{
  
  private $service;
  
  public function __construct( CustomerService $service )
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
    return view( 'admin.customers.index' );
  }
  
  /**
   * @param Customer $customer
   * @return JsonResponse
   */
  public function edit( Customer $customer ) : JsonResponse
  {
    return new JsonResponse( $customer );
  }// edit
}
