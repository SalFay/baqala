<?php

namespace App\Services;

use App\Helpers\Ui;
use App\Models\CustomerCredit;
use App\Repositories\CustomerCreditRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class CustomerCreditService
{
  private $repository;
  
  public function __construct( CustomerCreditRepository $repository )
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
    
    $table->addColumn( 'customer', static function( $row ) {
      return ucwords( $row->customer->first_name . ' ' . $row->customer->last_name );
    } );
    $table->addColumn( 'date', static function( $row ) {
      return date( 'd.m.Y H:i:s', strtotime( $row->created_at ) );
    } );
    $table->addColumn( 'action', static function( CustomerCredit $row ) {
      $buttons = [
        
        [
          'href'        => '#',
          'data-url'    => route( 'customerCredit.edit', $row->id ),
          'label'       => '<i class="fas fa-edit"></i>',
          'class'       => 'btn-primary btn-sm',
          'data-action' => 'edit'
        ],
        [
          'href'        => '#',
          'data-url'    => route( 'customerCredit.delete', $row->id ),
          'label'       => '<i class="fas fa-trash"></i>',
          'class'       => 'btn-danger btn-sm',
          'data-action' => 'delete'
        ],
      ];
      return Ui::actionButtons( $buttons );
    } );
    $table->rawColumns( [ 'action', 'customer', 'date' ] );
    return $table->make();
  }
  
}
