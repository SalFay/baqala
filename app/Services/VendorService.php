<?php

namespace App\Services;

use App\Helpers\Ui;
use App\Models\Vendor;
use App\Repositories\VendorRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class VendorService
{
  private $repository;
  
  public function __construct( VendorRepository $repository )
  {
    $this->repository = $repository;
  }
  
  /**
   * @param Request $request
   * @param DataTables $dataTables
   * @return JsonResponse
   * @throws Exception
   */
  public function dataTables( Request $request, DataTables $dataTables ) : JsonResponse
  {
    $table = $dataTables->eloquent( $this->repository->dataTablesQuery() );
    
    $table->addColumn( 'status', static function( $row ) {
      if( $row->status === 'Active' ) {
        return '<span class="badge bg-success">Active</span>';
      }
      
      return '<span class="badge bg-warning">Suspended</span>';
    } );
    $table->addColumn( 'debit', static function( $row ) {
      return checkingBalanceVendor( $row->id );
    } );
    $table->addColumn( 'action', static function( Vendor $row ) {
      
      $buttons = [
        /*  [
              'href'        => route( 'vendors.payments', $row->id ),
              'label'       => '<i class="fa fa-wallet"></i>',
              'class'       => 'btn-info btn-sm',
              'data-action' => 'payments'
          ],*/
        [
          'href'     => route( 'vendors.statement', $row->id ),
          'data-url' => '#',
          'label'    => '<i class="fas fa-wallet"></i>',
          'class'    => 'btn-info btn-sm'
        ],
        [
          'href'        => '#',
          'data-url'    => route( 'vendors.edit', $row->id ),
          'label'       => '<i class="fas fa-edit"></i>',
          'class'       => 'btn-primary btn-sm',
          'data-action' => 'edit'
        ],
        /* [
             'href'        => '#',
             'data-url'    => route( 'vendors.delete', $row->id ),
             'label'       => '<i class="fas fa-trash"></i>',
             'class'       => 'btn-danger btn-sm',
             'data-action' => 'delete'
         ],*/
        [
          'href'        => route( 'inventory.add', $row->id ),
          'label'       => '<i class="fa fa-cart-plus"></i>',
          'class'       => 'btn-success btn-sm',
          'data-action' => 'inventory',
          'title'       => 'Add Vendor Stock'
        ],
      ];
      return Ui::actionButtons( $buttons );
    } );
    $table->rawColumns( [ 'action', 'status', 'debit' ] );
    return $table->make();
  }
  
}
