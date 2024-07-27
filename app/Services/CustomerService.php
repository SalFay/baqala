<?php

namespace App\Services;

use App\Helpers\Ui;
use App\Models\Customer;
use App\Repositories\CustomerRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class CustomerService
{
  private $repository;
  
  public function __construct( CustomerRepository $repository )
  {
    $this->repository = $repository;
  }
  
  /**
   * @param Request $request
   * @param DataTables $dataTables
   * @return JsonResponse
   * @throws Exception
   */
  public function dataTables( Request $request, DataTables $dataTables )
  {
    $table = $dataTables->eloquent( $this->repository->dataTablesQuery() );
    
    $table->addColumn( 'status', static function( $row ) {
      if( $row->status === 'Active' ) {
        return '<span class="badge bg-success">Active</span>';
      } else {
        return '<span class="badge bg-warning">Suspended</span>';
        
      }
    } );
    
    $table->addColumn( 'debit', static function( $row ) {
      return checkingBalance( $row->id );
    } );
    $table->addColumn( 'action', static function( Customer $row ) {
      
      $buttons = [
        /*[
            'href'                  => route( 'customers.payments',
                $row->id ), 'label' => '<i class="fa fa-wallet"></i>',
            'class'                 => 'btn-info btn-sm',
            'data-action'           => 'payments'
        ],*/
        
        [
          'href'     => route( 'customers.statement', $row->id ),
          'data-url' => '#',
          'label'    => '<i class="fas fa-wallet"></i>',
          'class'    => 'btn-info btn-sm'
        ],
        [
          'href'        => '#',
          'data-url'    => route( 'customers.edit', $row->id ),
          'label'       => '<i class="fas fa-edit"></i>',
          'class'       => 'btn-primary btn-sm',
          'data-popup'  => 'tooltip',
          'title'       => 'Update Customer Details',
          'data-action' => 'edit'
        ],
        /*  [
              'href'        => '#',
              'data-url'    => route( 'customers.delete', $row->id ),
              'label'       => '<i class="fas fa-trash"></i>',
              'class'       => 'btn-danger btn-sm',
              'data-action' => 'delete',
              'data-popup'  => 'tooltip',
              'title'       => 'Delete Customer Details',
          ],*/
        [
          'href'        => route( 'order.add', $row->id ),
          'label'       => '<i class="fa fa-cart-plus"></i>',
          'class'       => 'btn-success btn-sm',
          'data-action' => 'orders',
          'data-popup'  => 'tooltip',
          'title'       => 'Update Customer Details',
        ],
      ];
      return Ui::actionButtons( $buttons );
    } );
    $table->rawColumns( [ 'action', 'status' ] );
    return $table->make();
  }
  
}
