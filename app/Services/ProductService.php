<?php

namespace App\Services;

use App\Helpers\Ui;
use App\Models\InventoryLog;
use App\Models\Order;
use App\Models\Product;
use App\Repositories\ProductRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Milon\Barcode\Facades\DNS1DFacade;
use Yajra\DataTables\DataTables;

class ProductService
{
  private $repository;

  public function __construct( ProductRepository $repository )
  {
    $this->repository = $repository;
  }

  public function availableStock( Request $request, DataTables $dataTables )
  {
    $query = $this->repository->dataTablesQuery();

    if( !empty( $request->search[ 'value' ] ) ) {
      $query->where( 'name', 'LIKE', '%' . $request->search[ 'value' ] . '%' );
    }
    $table = $dataTables->eloquent( $query->limit( 500 )->orderByDesc( 'id' ) );
    $table->addColumn( 'name', static function( $row ) {
      return $row->full_name;
    } )->addColumn( 'totalStock', static function( $row ) {
      return totalStock( $row->id );
    } )->addColumn( 'StockChecking', static function( $row ) {
      return StockChecking( $row->id, 0 );
    } )->addColumn( 'stockSold', static function( $row ) {
      return stockSold( $row->id );
    } )->addColumn( 'stockReturn', static function( $row ) {
      return stockReturn( $row->id );
    } );
    $table->rawColumns( [ 'name', 'totalStock', 'StockChecking', 'stockSold', 'stockReturn' ] );
    return $table->make();
  }

  public function inventoryLog( Request $request, DataTables $dataTables )
  {
    $start = startDate( $request->date );
    $end = endDate( $request->date );

    $query = InventoryLog::query();
    if( !empty( $request->search[ 'value' ] ) ) {
      $search = $request->search[ 'value' ];
      $query->where( 'order_type', 'LIKE', '%' . $search . '%' )
            ->orWhere( 'order_id', 'LIKE', '%' . $search . '%' )
            ->orWhere( 'status', 'LIKE', '%' . $search . '%' )
            ->orWhereHas( 'product', function( $query ) use ( $search ) {
              $query->where( 'name', 'LIKE', '%' . $search . '%' );
            } );
    }
    if( $start && $end ) {
      $query->where( 'created_at', '>=', $start )->where( 'created_at', '<=', $end );
    }
    $table = $dataTables->eloquent( $query->limit( 500 )->orderByDesc( 'id' ) );
    $table->addColumn( 'id', static function( $row ) {
      return $row->order_id;
    } )->addColumn( 'type', static function( $row ) {
      if( $row->order_type === \App\Models\Stock::class ) {
        return '<span class="badge bg-primary">Stock</span>';
      }
      return '<span class="badge bg-success">Order</span>';
    } )->addColumn( 'product', static function( $row ) {
      return $row->product->full_name ?? '';
    } )->addColumn( 'stock', static function( $row ) {
      return $row->stock;
    } )->addColumn( 'cost', static function( $row ) {
      return $row->cost;
    } )->addColumn( 'status', static function( $row ) {
      if( $row->status === 'Available' ) {
        return '<span class="badge bg-primary">Available</span>';
      } elseif( $row->status === 'Sold' ) {
        return '    <span class="badge bg-success">Sold</span>';
      } elseif( $row->status === 'Returned Vendor' ) {
        return '    <span class="badge bg-danger">Vendor Return</span>';
      }
      return '<span class="badge bg-danger">Order Return</span>';
    } )->addColumn( 'date', static function( $row ) {
      return Carbon::make( $row->created_at )->format( 'j F, Y, g:i a' );
    } );
    $table->rawColumns( [ 'id', 'type', 'product', 'stock', 'cost', 'status', 'date' ] );
    return $table->make();
  }

  public function orders( Request $request, DataTables $dataTables )
  {
    $start = startDate( $request->date );
    $end = endDate( $request->date );

    $query = Order::query();
    if( !empty( $request->search[ 'value' ] ) ) {
      $search = $request->search[ 'value' ];
      $query->where( 'id', 'LIKE', '%' . $search . '%' )
            ->orWhere( 'total', 'LIKE', '%' . $search . '%' )
            ->orWhereHas( 'customer', function( $query ) use ( $search ) {
              $query->where( 'first_name', 'LIKE', '%' . $search . '%' );
              $query->where( 'last_name', 'LIKE', '%' . $search . '%' );
            } );
    }
    if( $start && $end ) {
      $query->where( 'created_at', '>=', $start )->where( 'created_at', '<=', $end );
    }
    $table = $dataTables->eloquent( $query->limit( 500 )->orderByDesc( 'id' ) );
    $table->addColumn( 'customer', static function( $row ) {
      return $row->customer->first_name . ' ' . $row->customer->last_name;
    } )->addColumn( 'date', static function( $row ) {
      return Carbon::make( $row->created_at )->format( 'j F, Y, g:i a' );
    } );
    $table->rawColumns( [ 'customer', 'date' ] );
    return $table->make();
  }

}
