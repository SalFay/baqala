<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Services\AccountService;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class AccountController extends Controller
{
  private $service;
  
  public function __construct( AccountService $service )
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
    return view( 'admin.accounts.index' );
  }// index
  
  /**
   * @param Account $account
   * @return JsonResponse
   */
  public function edit( Account $account ) : JsonResponse
  {
    return new JsonResponse( $account );
  }// edit
  
}
