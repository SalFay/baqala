<?php

namespace App\Services;

use App\Helpers\Ui;
use App\Repositories\RoleRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class RoleService
{
  private $repository;
  
  public function __construct( RoleRepository $repository )
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
    
    $table->addColumn( 'action', static function( $row ) {
      $buttons = [
        [
          'href'        => '#',
          'data-url'    => route( 'roles.permissions.edit', $row->id ),
          'label'       => 'Permissions',
          'class'       => 'btn-sm btn-success',
          'data-action' => 'edit-permission'
        ],
        [
          'href'        => '#',
          'data-url'    => route( 'roles.edit', $row->id ),
          'label'       => '<i class="fas fa-edit"></i>',
          'class'       => 'btn-sm btn-primary',
          'data-action' => 'edit'
        ],
        /*[
            'href'        => '#',
            'data-url'    => route( 'roles.delete', $row->id ),
            'label'       => '<i class="fas fa-trash"></i>',
            'class'       => 'btn-sm btn-danger',
            'data-action' => 'delete'
        ],*/
      ];
      return Ui::actionButtons( $buttons );
    } );
    return $table->make();
  }
  
}
