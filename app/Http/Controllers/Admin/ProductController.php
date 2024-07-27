<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\ProductsDataTable;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class ProductController extends Controller
{
  
  private $service;
  
  public function __construct( ProductService $service )
  {
    $this->service = $service;
  }
  
  public function index( ProductsDataTable $dataTables )
  {
    return $dataTables->render( 'admin.products.index' );
  }
  
  public function print( Request $request )
  {
    $products = Product::where( 'status', 'Active' )->get();
    return view( 'admin.products.barcode', compact( 'products' ) );
  }
  
  public function sticker( Product $product )
  {
    return view( 'admin.products.sticker-barcode', compact( 'product' ) );
  }
  
  /**
   * @param Product $product
   * @return JsonResponse
   */
  public function edit( Product $product ) : JsonResponse
  {
    return new JsonResponse( $product );
  }// edit
  
}
