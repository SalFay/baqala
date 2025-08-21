<?php

namespace App\Services;

use App\Helpers\Ui;
use App\Models\Order;
use App\Models\Stock;
use Exception;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class InvoiceService
{

  /**
   * @param Request $request
   * @param DataTables $dataTables
   * @return mixed
   * @throws Exception
   */
  public function customerDataTables( Request $request, DataTables $dataTables )
  {
    $table = DataTables::of( Order::query()->orderBy( 'id', 'DESC' )->limit( 500 ) );

    $table->addColumn( 'customer', static function( $row ) {
      return $row->customer->full_name;
    } );

    $table->addColumn( 'action', static function( Order $row ) {

      $buttons = [

        [
          'href'  => route( 'order.invoice', $row->id ),
          'label' => '<i class="fas fa-eye"></i>',
          'class' => 'btn-primary btn-sm'
        ],
       /* [
          'href'  => route( 'orders.edit', $row->id ),
          'label' => '<i class="fas fa-edit"></i>',
          'class' => 'btn-info btn-sm'
        ],*/ /*[
          'href'        => '#',
          'data-url'    => route( 'order.delete', $row->id ),
          'label'       => '<i class="fas fa-trash"></i>',
          'class'       => 'btn-danger btn-sm',
          'data-action' => 'delete'
        ]*/
      ];
      return Ui::actionButtons( $buttons );
    } );
    $table->rawColumns( [ 'customer', 'action' ] );
    return $table->make();
  }

  public function vendorDataTables( Request $request, DataTables $dataTables )
  {
    $table = DataTables::of( Stock::query()->orderBy( 'id', 'DESC' )->limit( 500 ) );

    $table->addColumn( 'vendor', static function( $row ) {
      return $row->vendor->name;
    } );

    $table->addColumn( 'action', static function( Stock $row ) {

      $buttons = [

        [
          'href'  => route( 'inventory.invoice', $row->id ),
          'label' => '<i class="fas fa-eye"></i>',
          'class' => 'btn-primary btn-sm'
        ]
      ];
      return Ui::actionButtons( $buttons );
    } );
    $table->rawColumns( [ 'vendor', 'action' ] );
    return $table->make();
  }

}
