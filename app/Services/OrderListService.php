<?php

namespace App\Services;

use App\Models\Order;
use App\Support\DateRange;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

/**
 * Orders list (Reports > Order): one set of filters drives the totals, the table
 * and its footer, so the figures always agree with the rows shown.
 */
class OrderListService
{
  public const SHOW = [
    ''           => 'All orders',
    'unpaid'     => 'Unpaid (on credit)',
    'discounted' => 'With a discount',
    'delivery'   => 'With delivery charges',
  ];

  /** The filters from the request, cleaned. */
  public function filters( Request $request ) : array
  {
    $show = (string) $request->input( 'show', '' );
    return [
      'range'   => DateRange::parse( $request->input( 'range' ), 'today' ),
      'cashier' => trim( (string) $request->input( 'cashier', '' ) ),
      'search'  => trim( (string) $request->input( 'q', '' ) ),
      'show'    => array_key_exists( $show, self::SHOW ) ? $show : '',
    ];
  }

  private function base( array $f ) : Builder
  {
    $q = DB::table( 'orders AS o' )->whereNull( 'o.deleted_at' )->whereBetween( 'o.date', $f[ 'range' ]->dates() );

    if( $f[ 'cashier' ] === '-' ) {
      $q->where( fn( $w ) => $w->whereNull( 'o.cashier_name' )->orWhere( 'o.cashier_name', '' ) );
    } elseif( $f[ 'cashier' ] !== '' ) {
      $q->whereRaw( 'UPPER(TRIM(o.cashier_name)) = ?', [ mb_strtoupper( $f[ 'cashier' ] ) ] );
    }

    if( $f[ 'search' ] !== '' ) {
      $s = $f[ 'search' ];
      $q->where( function( $w ) use ( $s ) {
        $w->where( 'o.customer_name', 'LIKE', '%' . $s . '%' );
        if( ctype_digit( ltrim( $s, '#' ) ) ) {
          $w->orWhere( 'o.id', (int) ltrim( $s, '#' ) );
        }
      } );
    }

    switch( $f[ 'show' ] ) {
      case 'unpaid':
        $q->where( 'o.debit', '>', 0 );
        break;
      case 'discounted':
        $q->whereRaw( Order::DISCOUNT_SQL . ' > 0' );
        break;
      case 'delivery':
        $q->where( 'o.delivery_charges', '>', 0 );
        break;
    }
    return $q;
  }

  /** Totals over every order the filters match (not just one page). */
  public function summary( array $f ) : array
  {
    $o = $this->base( $f )->selectRaw( 'COUNT(*) AS orders,
        COALESCE(SUM(o.sub_total), 0) AS item_sales,
        COALESCE(SUM(GREATEST(0, o.sub_total + COALESCE(o.delivery_charges, 0) - o.total)), 0) AS discount,
        COALESCE(SUM(o.delivery_charges), 0) AS delivery,
        COALESCE(SUM(o.total), 0) AS net_sales,
        COALESCE(SUM(o.credit), 0) AS paid,
        COALESCE(SUM(o.debit), 0) AS unpaid' )->first();

    $items = (int) DB::table( 'order_items AS i' )
                     ->where( 'i.order_type', Order::class )->whereNull( 'i.deleted_at' )
                     ->whereIn( 'i.order_id', $this->base( $f )->select( 'o.id' ) )
                     ->sum( 'i.stock' );

    $orders = (int) $o->orders;
    return [
      'orders'     => $orders,
      'items'      => $items,
      'item_sales' => (float) $o->item_sales,
      'discount'   => (float) $o->discount,
      'delivery'   => (float) $o->delivery,
      'net_sales'  => (float) $o->net_sales,
      'paid'       => (float) $o->paid,
      'unpaid'     => (float) $o->unpaid,
      'avg_order'  => $orders ? (float) $o->net_sales / $orders : 0,
    ];
  }

  /** Cashier names used in the last year, merged across spelling/case, busiest first. */
  public function cashiers() : array
  {
    return DB::table( 'orders' )->whereNull( 'deleted_at' )
             ->where( 'date', '>=', now()->subYear()->toDateString() )
             ->whereNotNull( 'cashier_name' )->where( 'cashier_name', '<>', '' )
             ->groupByRaw( 'UPPER(TRIM(cashier_name))' )
             ->selectRaw( 'UPPER(TRIM(cashier_name)) AS name, COUNT(*) AS orders' )
             ->orderByDesc( 'orders' )->limit( 50 )
             ->pluck( 'orders', 'name' )->all();
  }

  public function dataTable( Request $request, DataTables $dataTables )
  {
    capDataTablesLength( $request );
    $f = $this->filters( $request );

    $lineQty = DB::table( 'order_items AS i' )->selectRaw( 'COALESCE(SUM(i.stock), 0)' )
                 ->whereColumn( 'i.order_id', 'o.id' )->where( 'i.order_type', Order::class )->whereNull( 'i.deleted_at' );

    $query = $this->base( $f )
                  ->select( 'o.id', 'o.date', 'o.created_at', 'o.customer_name', 'o.cashier_name', 'o.sub_total',
                    'o.delivery_charges', 'o.total', 'o.credit', 'o.debit', 'o.discount', 'o.discount_type' )
                  ->selectRaw( Order::DISCOUNT_SQL . ' AS discount_amount' )
                  ->selectSub( $lineQty, 'items' )
                  ->orderByDesc( 'o.date' )->orderByDesc( 'o.id' );

    $canView = user_can( 'access customer invoice' );
    $canEdit = user_can( 'update customer invoice' );
    $e = fn( $v ) => e( (string) $v );

    return $dataTables->query( $query )
                      ->filter( fn() => null ) // searching is the "q" filter above, applied to the totals too
                      ->editColumn( 'id', fn( $r ) => $canView
                        ? '<a href="' . route( 'order.invoice', $r->id ) . '" target="_blank">#' . $r->id . '</a>'
                        : '#' . $r->id )
                      ->addColumn( 'when', fn( $r ) => \Carbon\Carbon::parse( $r->date )->format( 'd-M-Y' )
                        . '<div class="rk-sub">' . ( $r->created_at ? \Carbon\Carbon::parse( $r->created_at )->format( 'g:i a' ) : '' ) . '</div>' )
                      ->editColumn( 'customer_name', fn( $r ) => $r->customer_name !== null && trim( $r->customer_name ) !== ''
                        ? $e( $r->customer_name ) : '<span class="rk-sub">Walk-in</span>' )
                      ->editColumn( 'cashier_name', fn( $r ) => $r->cashier_name !== null && trim( $r->cashier_name ) !== ''
                        ? $e( $r->cashier_name ) : '<span class="rk-sub">—</span>' )
                      ->editColumn( 'discount_amount', function( $r ) {
                        $amount = (float) $r->discount_amount;
                        if( $amount <= 0 ) {
                          return '<span class="rk-sub">0</span>';
                        }
                        $note = $r->discount_type === 'per' && $r->discount > 0 ? '<div class="rk-sub">' . (float) $r->discount . '%</div>' : '';
                        return number_format( $amount, $amount == floor( $amount ) ? 0 : 2 ) . $note;
                      } )
                      ->addColumn( 'action', function( $r ) use ( $canView, $canEdit ) {
                        $out = '';
                        if( $canView ) {
                          $out .= '<a href="' . route( 'order.invoice', $r->id ) . '" target="_blank" class="btn btn-sm btn-light" title="View / print invoice"><i class="fas fa-eye"></i></a> ';
                        }
                        if( $canEdit ) {
                          $out .= '<a href="' . route( 'orders.edit', $r->id ) . '" class="btn btn-sm btn-light" title="Edit order"><i class="fas fa-edit"></i></a>';
                        }
                        return $out;
                      } )
                      ->rawColumns( [ 'id', 'when', 'customer_name', 'cashier_name', 'discount_amount', 'action' ] )
                      ->make( true );
  }
}
