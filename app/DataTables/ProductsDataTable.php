<?php

namespace App\DataTables;

use App\Models\Post;
use App\Models\Product;
use App\Models\Role;
use App\Models\VerifiedNumbers;
use Carbon\Carbon;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ProductsDataTable extends DataTable
{
  /**
   * @param $query
   * @return DataTableAbstract|\Yajra\DataTables\EloquentDataTable
   */
  public function dataTable( $query )
  {
    return datatables()
      ->eloquent( $query )
      ->addIndexColumn()
      ->editColumn( 'category', function( $row ) {
        return $row->category->name;
      } )->editColumn( 'status', function( $row ) {
        return $row->status === 'Active' ? '<span class="badge badge-success">' . $row->status . '</span>' : '<span class="badge badge-warning">' . $row->status . '</span>';
      } )->addColumn( 'action', function( $row ) {
        return view( 'admin.products.actions', [ 'id' => $row->id ] )->render();
      } )->rawColumns( [ 'category', 'action', 'status' ] );
  }
  
  /**
   * @param Product $model
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function query( Product $model )
  {
    $query = $model->newQuery();
    $category = request( 'category_id' );
    if( !empty( $category ) ) {
      $query = $query->where( "category_id", $category );
    }
    return $query->latest();
  }
  
  /**
   * Optional method if you want to use html builder.
   * @return Builder
   */
  public function html()
  {
    return $this->builder()
                ->setTableId( 'products-table' )
                ->setTableAttribute( 'style', 'width:100%' )
                ->columns( $this->getColumns() )
                ->minifiedAjax()
                ->scrollX( true )
                ->dom( '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-3"l><"col-sm-12 col-md-3"B><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 mb-1 row"<"col-sm-12 col-md-3"i><"col-sm-12 col-md-3"p>>' )
                ->orderBy( 1, 'asc' )
                ->buttons(
                  Button::make( 'print' ),
                  Button::make( 'excel' ),
                )
                ->ajaxWithForm( null, '#filter-form' );
  }
  
  /**
   * Get columns.
   * @return array
   */
  protected function getColumns()
  {
    return [
      Column::make( 'DT_RowIndex' )->title( "#" )->searchable( false )->orderable( false ),
      Column::make( 'name' )->title( 'Name' ),
      Column::make( 'arabic_name' )->title( 'Arabic' ),
      Column::make( 'category' )->searchable( false ),
      Column::make( 'purchase_price' )->title( 'Purchase Price' ),
      Column::make( 'taxable_price' )->title( 'Taxable Price' ),
      Column::make( 'pid' )->title( 'Barcode' ),
      Column::make( 'status' )->title( 'Status' ),
      Column::make( 'action' )->title( 'Action' )
    ];
  }
  
  /**
   * Get filename for export.
   * @return string
   */
  protected function filename()
  {
    return 'products_' . date( 'YmdHis' );
  }
}
