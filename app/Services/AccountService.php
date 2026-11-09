<?php

namespace App\Services;

use App\Helpers\Ui;
use App\Models\Account;
use App\Models\Bank;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Vendor;
use App\Repositories\AccountRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class AccountService
{
  private $repository;
  
  public function __construct( AccountRepository $repository )
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
    capDataTablesLength( $request );
    $start = startDate( $request->date );
    $end = endDate( $request->date );
    
    $type = (string) $request->input( 'type', '' );
    $source = (string) $request->input( 'source', '' );
    $query = $this->repository->dataTablesQuery( $start, $end, $type, $source );

    // Totals over every matching entry (the footer used to add up only the page shown).
    $totals = ( clone $query )->reorder()->toBase()
                ->selectRaw( 'COUNT(*) AS entries, COALESCE(SUM(debit), 0) AS debit, COALESCE(SUM(credit), 0) AS credit,
                    COALESCE(SUM(discount), 0) AS discount' )->first();

    $table = $dataTables->eloquent( $query->with( 'bank' ) )->with( 'totals', [
      'entries'  => (int) $totals->entries,
      'debit'    => (float) $totals->debit,
      'credit'   => (float) $totals->credit,
      'discount' => (float) $totals->discount,
    ] );
    
    $table->addColumn( 'type', static function( $row ) {
      $type = '';
      if( $row->party_type === Customer::class ) {
        $type = 'Customer';
      }
      if( $row->party_type === Vendor::class ) {
        $type = 'Vendor';
      }
      
      if( $row->party_type === Expense::class ) {
        $type = 'Expense';
      }
      
      if( $row->party_type === Bank::class ) {
        $type = 'Bank';
      }
      return $type;
    } );
    
    $table->addColumn( 'name', static function( $row ) {
      $name = '';
      if( $row->party_type === Customer::class ) {
        $name = name( 'Customer', $row->party_id );
      }
      if( $row->party_type === Vendor::class ) {
        $name = name( 'Vendor', $row->party_id );
      }
      if( $row->party_type === Bank::class ) {
        $name = name( 'Bank', $row->party_id );
      }
      
      if( $row->party_type === Expense::class ) {
        $name = name( 'Expense', $row->party_id );
      }
      return $name;
    } );
    
    $table->addColumn( 'date', static function( $row ) {
      return date( 'd-M-Y', strtotime( $row->created_at ) )
        . '<div class="rk-sub">' . date( 'g:i a', strtotime( $row->created_at ) ) . '</div>';
    } );
    
    $table->addColumn( 'bank', static function( $row ) {
      if( $row->bank ) {
        return $row->bank->name . '-' . $row->cheque;
      } elseif( $row->bank_id === 0 ) {
        return 'Cash';
      } else {
        return '';
      }
    } );
    
    $table->addColumn( 'action', static function( Account $row ) {
      
      $buttons = [
        
        [
          'href'        => '#',
          'data-url'    => route( 'accounts.edit', $row->id ),
          'label'       => '<i class="fas fa-edit"></i>',
          'class'       => 'btn-primary btn-sm',
          'data-action' => 'edit'
        ],
        /*[
            'href'        => '#',
            'data-url'    => route( 'accounts.delete', $row->id ),
            'label'       => '<i class="fas fa-trash"></i>',
            'class'       => 'btn-danger btn-sm',
            'data-action' => 'delete'
        ],*/
      ];
      return Ui::actionButtons( $buttons );
    } );
    $table->rawColumns( [ 'action', 'type', 'name', 'date', 'bank' ] );
    return $table->make();
  }
  
}
