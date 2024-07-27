<?php

namespace App\Services;

use App\Helpers\Ui;
use App\Models\User;
use App\Repositories\UserRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class UserService
{
  private $repository;
  
  public function __construct( UserRepository $repository )
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
    
    $table->addColumn( 'user_roles', static function( $row ) {
      return $row->role->name;
    } );
    
    $table->addColumn( 'verified', static function( $row ) {
      return empty( $row->verified ) ? '<i class="text-danger fas fa-times-circle fa-2x"></i>' : '<i class="text-success fas fa-check-circle fa-2x"></i>';
    } );
    $table->addColumn( 'status', static function( $row ) {
      if( $row->status == 'Active' ) {
        return '<span class="badge bg-success">Active</span>';
      } else {
        return '<span class="badge bg-warning">Suspended</span>';
        
      }
    } );
    
    $table->addColumn( 'action', static function( User $row ) {
      $verified = [];
      $impersonate = [];
      if( empty( $row->verified ) ) {
        $verified[] = [
          'href'        => '#',
          'data-url'    => route( 'users.verify', $row->id ),
          'label'       => '<i class="fas fa-check"></i>',
          'class'       => 'btn-success btn-sm',
          'data-action' => 'verify'
        ];
      }
      
      if( user()->id !== $row->id ) {
        $impersonate[] = [
          'href'  => route( 'impersonate.start', $row->id ),
          'label' => '<i class="fas fa-sign-in-alt"></i>',
          'class' => 'btn-default',
          'can'   => 'edit user',
          'title' => 'Login as this User',
        ];
      }
      
      $buttons = [
        
        [
          'href'        => '#',
          'data-url'    => route( 'users.edit', $row->id ),
          'label'       => '<i class="fas fa-edit"></i>',
          'class'       => 'btn-primary btn-sm',
          'data-action' => 'edit'
        ],
        /*  [
              'href'        => '#',
              'data-url'    => route( 'users.delete', $row->id ),
              'label'       => '<i class="fas fa-trash"></i>',
              'class'       => 'btn-danger btn-sm',
              'data-action' => 'delete'
          ],*/
      ];
      return Ui::actionButtons( array_merge( $impersonate, $verified, $buttons ) );
    } );
    $table->rawColumns( [ 'user_roles', 'action', 'verified', 'status' ] );
    
    return $table->make();
  }
  
}
