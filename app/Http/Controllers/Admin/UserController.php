<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class UserController extends Controller
{
  
  private $service;
  
  public function __construct( UserService $service )
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
    return view( 'admin.users.index' );
  }
  
  /**
   * @param $user
   * @return User
   */
  public function edit( $user ) : User
  {
    return User::findOrFail( $user );
  }// edit
  
}
