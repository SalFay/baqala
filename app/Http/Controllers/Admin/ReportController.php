<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryLog;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Stock;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class ReportController extends Controller
{
  
  private $service;
  
  public function __construct( ProductService $service )
  {
    $this->service = $service;
  }
  
  public function availableStock( Request $request, DataTables $dataTables )
  {
    
    if( $request->ajax() && $request->isMethod( 'post' ) ) {
      return $this->service->availableStock( $request, $dataTables );
    }
    return view( 'admin.reports.availableStock' );
    
  }
  
  public function orders( Request $request, DataTables $dataTables )
  {
    if( $request->ajax() && $request->isMethod( 'post' ) ) {
      return $this->service->orders( $request, $dataTables );
    }
    return view( 'admin.reports.orders' );
    
  }
  
  public function ordersItem()
  {
    $orders = OrderItem::all();
    return view( 'admin.reports.ordersItem', compact( 'orders' ) );
    
  }
  
  public function stock()
  {
    $stock = Stock::all();
    return view( 'admin.reports.stock', compact( 'stock' ) );
    
  }
  
  public function inventory( Request $request, DataTables $dataTables )
  {
    if( $request->ajax() && $request->isMethod( 'post' ) ) {
      return $this->service->inventoryLog( $request, $dataTables );
    }
    return view( 'admin.reports.inventory' );
    
  }
}
