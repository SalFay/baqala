<?php

namespace App\Services;

use App\Helpers\Ui;
use App\Models\VendorCredit;
use App\Repositories\VendorCreditRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class VendorCreditService
{
  private $repository;
  
  public function __construct( VendorCreditRepository $repository )
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
    
    $table->addColumn( 'vendor', static function( $row ) {
      return ucwords( $row->vendor->name );
    } );
    $table->addColumn( 'date', static function( $row ) {
      return date( 'd.m.Y H:i:s', strtotime( $row->created_at ) );
    } );
    $table->addColumn( 'action', static function( VendorCredit $row ) {
      $buttons = [
        
        [
          'href'        => '#',
          'data-url'    => route( 'vendorCredit.edit', $row->id ),
          'label'       => '<i class="fas fa-edit"></i>',
          'class'       => 'btn-primary btn-sm',
          'data-action' => 'edit'
        ],
        [
          'href'        => '#',
          'data-url'    => route( 'vendorCredit.delete', $row->id ),
          'label'       => '<i class="fas fa-trash"></i>',
          'class'       => 'btn-danger btn-sm',
          'data-action' => 'delete'
        ],
      ];
      return Ui::actionButtons( $buttons );
    } );
    $table->rawColumns( [ 'action', 'vendor', 'date' ] );
    return $table->make();
  }
  
}
