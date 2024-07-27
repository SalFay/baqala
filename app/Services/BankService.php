<?php

namespace App\Services;

use App\Helpers\Ui;
use App\Models\Bank;
use App\Repositories\BankRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class BankService
{
  private $repository;
  
  public function __construct( BankRepository $repository )
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
    
    $table->addColumn( 'action', static function( Bank $row ) {
      
      $buttons = [
        
        [
          'href'     => route( 'bank.statement', $row->id ),
          'data-url' => '#',
          'label'    => '<i class="fas fa-wallet"></i>',
          'class'    => 'btn-info btn-sm'
        ],
        [
          'href'        => '#',
          'data-url'    => route( 'bank.edit', $row->id ),
          'label'       => '<i class="fas fa-edit"></i>',
          'class'       => 'btn-primary btn-sm',
          'data-action' => 'edit'
        ],
        /*[
            'href'        => '#',
            'data-url'    => route( 'bank.delete', $row->id ),
            'label'       => '<i class="fas fa-trash"></i>',
            'class'       => 'btn-danger btn-sm',
            'data-action' => 'delete'
        ],*/
      ];
      return Ui::actionButtons( $buttons );
    } );
    $table->rawColumns( [ 'action' ] );
    return $table->make();
  }
  
}
