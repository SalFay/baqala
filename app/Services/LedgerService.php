<?php

namespace App\Services;

use App\Models\Bank;
use App\Models\Customer;
use App\Models\Vendor;
use App\Support\DateRange;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Statements (ledgers) of a customer, vendor or bank from the accounts table, for a
 * date range: opening balance carried in from before the range, the entries with a
 * running balance, and the closing balance. Same balance rules as the old statements:
 *   customer: debit - credit - discount (what the customer owes)
 *   vendor:   credit - debit            (what is owed to the vendor)
 *   bank:     credit - debit            (its own rows and payments made through it)
 */
class LedgerService
{
  public const PARTIES = [
    'customer' => [ 'model' => Customer::class, 'balance' => 'Owed by customer', 'route' => 'customers.statement' ],
    'vendor'   => [ 'model' => Vendor::class, 'balance' => 'Owed to vendor', 'route' => 'vendors.statement' ],
    'bank'     => [ 'model' => Bank::class, 'balance' => 'Balance', 'route' => 'bank.statement' ],
  ];

  private function amountSql( string $kind ) : string
  {
    return $kind === 'customer'
      ? '(COALESCE(a.debit, 0) - COALESCE(a.credit, 0) - COALESCE(a.discount, 0))'
      : '(COALESCE(a.credit, 0) - COALESCE(a.debit, 0))';
  }

  private function entries( string $kind, int $id ) : Builder
  {
    $model = self::PARTIES[ $kind ][ 'model' ];
    return DB::table( 'accounts AS a' )->whereNull( 'a.deleted_at' )
             ->where( function( $w ) use ( $kind, $model, $id ) {
               $w->where( fn( $p ) => $p->where( 'a.party_type', $model )->where( 'a.party_id', $id ) );
               if( $kind === 'bank' ) {
                 $w->orWhere( 'a.bank_id', $id );
               }
             } );
  }

  private function inRange( Builder $q, DateRange $range, string $search ) : Builder
  {
    $q->whereBetween( 'a.created_at', $range->moments() );
    if( $search !== '' ) {
      $q->where( 'a.comments', 'LIKE', '%' . $search . '%' );
    }
    return $q;
  }

  public function filters( Request $request ) : array
  {
    return [
      'range'  => DateRange::parse( $request->input( 'range' ), 'month' ),
      'search' => trim( (string) $request->input( 'q', '' ) ),
    ];
  }

  public function summary( string $kind, int $id, array $f ) : array
  {
    $amount = $this->amountSql( $kind );
    $opening = (float) $this->entries( $kind, $id )
                            ->where( 'a.created_at', '<', $f[ 'range' ]->moments()[ 0 ] )
                            ->sum( DB::raw( $amount ) );

    $r = $this->inRange( $this->entries( $kind, $id ), $f[ 'range' ], '' )
              ->selectRaw( "COUNT(*) AS n, COALESCE(SUM(a.debit), 0) AS debit, COALESCE(SUM(a.credit), 0) AS credit,
                  COALESCE(SUM(a.discount), 0) AS discount, COALESCE(SUM($amount), 0) AS movement" )
              ->first();

    $closing = $opening + (float) $r->movement;
    return [
      'opening'  => $opening,
      'debit'    => (float) $r->debit,
      'credit'   => (float) $r->credit,
      'discount' => (float) $r->discount,
      'closing'  => $closing,
      'entries'  => (int) $r->n,
      'all_time' => (float) $this->entries( $kind, $id )->sum( DB::raw( $amount ) ),
    ];
  }

  /**
   * One page of entries (oldest first) with the running balance, in DataTables' format.
   * The balance before the page = opening + every earlier entry in the range.
   */
  public function page( string $kind, int $id, Request $request ) : array
  {
    $f = $this->filters( $request );
    $amount = $this->amountSql( $kind );
    $start = max( 0, (int) $request->input( 'start', 0 ) );
    $length = (int) $request->input( 'length', 100 );
    $length = $length <= 0 || $length > 1000 ? 1000 : $length;

    $base = fn() => $this->inRange( $this->entries( $kind, $id ), $f[ 'range' ], $f[ 'search' ] )
                         ->orderBy( 'a.created_at' )->orderBy( 'a.id' );

    $total = $this->inRange( $this->entries( $kind, $id ), $f[ 'range' ], $f[ 'search' ] )->count();
    $rows = $base()->offset( $start )->limit( $length )
                   ->get( [ 'a.id', 'a.created_at', 'a.comments', 'a.cheque', 'a.debit', 'a.credit', 'a.discount', DB::raw( "$amount AS amount" ) ] );

    // Running balance only makes sense without a search (a search skips entries).
    $running = null;
    if( $f[ 'search' ] === '' ) {
      $opening = (float) $this->entries( $kind, $id )->where( 'a.created_at', '<', $f[ 'range' ]->moments()[ 0 ] )->sum( DB::raw( $amount ) );
      $before = $start > 0
        ? (float) DB::query()->fromSub( $base()->limit( $start )->select( DB::raw( "$amount AS amount" ) ), 'p' )->sum( 'p.amount' )
        : 0.0;
      $running = $opening + $before;
    }

    $canInvoice = user_can( 'access customer invoice' );
    $data = [];
    foreach( $rows as $r ) {
      if( $running !== null ) {
        $running += (float) $r->amount;
      }
      $comment = e( (string) $r->comments );
      if( $kind === 'customer' && $canInvoice && preg_match( '/^Order No\.(\d+)$/', (string) $r->comments, $m ) ) {
        $comment = '<a href="' . route( 'order.invoice', $m[ 1 ] ) . '" target="_blank">Order #' . $m[ 1 ] . '</a>';
      }
      $data[] = [
        'date'     => \Carbon\Carbon::parse( $r->created_at )->format( 'd-M-Y' )
          . '<div class="rk-sub">' . \Carbon\Carbon::parse( $r->created_at )->format( 'g:i a' ) . '</div>',
        'comments' => $comment . ( $r->cheque ? '<div class="rk-sub">Cheque ' . e( $r->cheque ) . '</div>' : '' ),
        'debit'    => (float) $r->debit,
        'credit'   => (float) $r->credit,
        'discount' => (float) $r->discount,
        'balance'  => $running,
      ];
    }

    return [
      'draw'            => (int) $request->input( 'draw', 1 ),
      'recordsTotal'    => $total,
      'recordsFiltered' => $total,
      'data'            => $data,
    ];
  }
}
