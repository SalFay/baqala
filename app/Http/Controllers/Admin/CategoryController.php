<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\CategoryService;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class CategoryController extends Controller
{
  private $service;
  
  public function __construct( CategoryService $service )
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
    return view( 'admin.categories.index' );
  }// index
  
  /**
   * @param Category $category
   * @return JsonResponse
   */
  public function edit( Category $category ) : JsonResponse
  {
    return new JsonResponse( $category );
  }// edit
  
}
