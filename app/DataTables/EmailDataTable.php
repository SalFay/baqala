<?php

namespace App\DataTables;

use App\Models\Post;
use App\Models\Role;
use App\Models\VerifiedNumbers;
use Carbon\Carbon;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class EmailDataTable extends DataTable
{
  /**
   * Build DataTable class.
   * @param mixed $query Results from query() method.
   * @return DataTableAbstract
   */
  public function dataTable( $query )
  {
    return datatables()
      ->eloquent( $query )
      ->addIndexColumn()
      ->editColumn( 'properties', function( $row ) {
        return array_values( convert_object_to_array( json_decode( $row->properties ) ) );
      } )->editColumn( 'description', function( $row ) {
        return $row->description;
      } )->editColumn( 'status', function( $row ) {
        if( $row->log_name === 'not-send-email-manual' || $row->log_name === 'not-send-email' ) {
          return '<label class="badge btn-danger">Rejected</label>';
        }
        return '<label class="badge btn-success">Delivered</label>';
      } )
      ->addColumn( 'date', function( $row ) {
        return Carbon::make( $row->created_at )->format( 'd-m-Y' );
      } )->rawColumns( [ 'status', 'message', 'number', 'date', 'type' ] );
  }
  
  /**
   * @param Activity $model
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function query( Activity $model )
  {
    $date = Carbon::now()->subDays( 3 );
    $query = $model->newQuery();
    $array = [ 'send-email-manual', 'not-send-email-manual', 'registered-email', 'send-email' ];
    $query->whereIn( 'log_name', $array )->orderByDesc( 'id' );
    return $query->orderByDesc( 'id' )->where( 'created_at', '>=', $date );
  }
  
  /**
   * Optional method if you want to use html builder.
   * @return Builder
   */
  public function html()
  {
    return $this->builder()
                ->setTableId( 'email-table' )
                ->setTableAttribute( 'style', 'width:100%' )
                ->columns( $this->getColumns() )
                ->minifiedAjax()
                ->scrollX( true )
                ->lengthMenu( [ [ 10, 25, 50, -1 ], [ 10, 25, 50, "All" ] ] )
                ->dom( '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-3"l><"col-sm-12 col-md-3"B><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 mb-1 row"<"col-sm-12 col-md-3"i><"col-sm-12 col-md-3"p>>' )
                ->orderBy( 1, 'asc' )
                ->buttons(
                  Button::make( 'pdf' ),
                )->ajaxWithForm( null, '#filter-form' );
  }
  
  /**
   * Get columns.
   * @return array
   */
  protected function getColumns()
  {
    return [
      Column::make( 'DT_RowIndex' )->title( "#" )->searchable( false )->orderable( false ),
      Column::make( 'properties' )->title( 'Email' ),
      Column::make( 'description' )->title( 'Description' ),
      Column::make( 'status' )->searchable( false ),
      Column::make( 'date' )->searchable( false ),
    ];
  }
  
  /**
   * Get filename for export.
   * @return string
   */
  protected function filename()
  {
    return 'Email_' . date( 'YmdHis' );
  }
}
