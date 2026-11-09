<?php

namespace App\Services;

use App\Models\Order;
use App\Support\DateRange;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

/**
 * Item-level sales reports (Margin / Profit and Product Sold). Every figure is on the
 * sale date (orders.date), like the dashboard and the Orders list.
 *
 * Cost: the POS saves the product's purchase price on each line, and today that is the
 * sale price (products have no real cost). A line only counts towards profit when it has
 * a real cost: above 0 and below the sale price. Otherwise profit is not guessed.
 */
class SalesReportService
{
  public const COSTED = 'oi.purchase_price > 0 AND oi.purchase_price < oi.sale_price';

  public function filters( Request $request, string $default = 'today' ) : array
  {
    return [
      'range'    => DateRange::parse( $request->input( 'range' ), $default ),
      'category' => (int) $request->input( 'category', 0 ),
      'search'   => trim( (string) $request->input( 'q', '' ) ),
    ];
  }

  /** Order lines in the range, with product and category, filtered. */
  private function lines( array $f ) : Builder
  {
    $q = DB::table( 'order_items AS oi' )
           ->join( 'orders AS o', 'o.id', '=', 'oi.order_id' )
           ->leftJoin( 'products AS p', 'p.id', '=', 'oi.product_id' )
           ->leftJoin( 'categories AS c', 'c.id', '=', 'p.category_id' )
           ->where( 'oi.order_type', Order::class )
           ->whereNull( 'oi.deleted_at' )->whereNull( 'o.deleted_at' )
           ->whereBetween( 'o.date', $f[ 'range' ]->dates() );

    if( $f[ 'category' ] > 0 ) {
      $q->where( 'p.category_id', $f[ 'category' ] );
    }
    if( $f[ 'search' ] !== '' ) {
      $s = $f[ 'search' ];
      $q->where( function( $w ) use ( $s ) {
        $w->where( 'p.name', 'LIKE', '%' . $s . '%' );
        if( ctype_digit( ltrim( $s, '#' ) ) ) {
          $w->orWhere( 'oi.order_id', (int) ltrim( $s, '#' ) );
        }
      } );
    }
    return $q;
  }

  /**
   * SQL for a line's share of its order's discount, by sale value. Needs the join to
   * the per-order line totals (alias "ot") added by withOrderTotals().
   */
  private const DISCOUNT_SHARE = 'CASE WHEN ot.line_sales > 0
      THEN GREATEST(0, o.sub_total + COALESCE(o.delivery_charges, 0) - o.total) * (oi.stock * oi.sale_price) / ot.line_sales
      ELSE 0 END';

  private function withOrderTotals( Builder $q, array $f ) : Builder
  {
    // Line totals of every order in the range (all its lines, not only the filtered ones).
    $totals = DB::table( 'order_items AS x' )
                ->join( 'orders AS xo', 'xo.id', '=', 'x.order_id' )
                ->where( 'x.order_type', Order::class )->whereNull( 'x.deleted_at' )->whereNull( 'xo.deleted_at' )
                ->whereBetween( 'xo.date', $f[ 'range' ]->dates() )
                ->groupBy( 'x.order_id' )
                ->selectRaw( 'x.order_id, SUM(x.stock * x.sale_price) AS line_sales' );

    return $q->joinSub( $totals, 'ot', 'ot.order_id', '=', 'oi.order_id' );
  }

  /** Totals for the filtered lines. */
  public function summary( array $f ) : array
  {
    $r = $this->withOrderTotals( $this->lines( $f ), $f )
              ->selectRaw( 'COUNT(*) AS line_count,
                  COUNT(DISTINCT oi.order_id) AS orders,
                  COUNT(DISTINCT oi.product_id) AS products,
                  COALESCE(SUM(oi.stock), 0) AS items,
                  COALESCE(SUM(oi.stock * oi.sale_price), 0) AS sales,
                  COALESCE(SUM(' . self::DISCOUNT_SHARE . '), 0) AS discount,
                  COALESCE(SUM(CASE WHEN ' . self::COSTED . ' THEN oi.stock * oi.sale_price END), 0) AS costed_sales,
                  COALESCE(SUM(CASE WHEN ' . self::COSTED . ' THEN oi.stock * oi.purchase_price END), 0) AS costed_cost,
                  COALESCE(SUM(CASE WHEN ' . self::COSTED . ' THEN ' . self::DISCOUNT_SHARE . ' END), 0) AS costed_discount' )
              ->first();

    $sales = (float) $r->sales;
    $costedSales = (float) $r->costed_sales;
    $profit = $costedSales > 0 ? $costedSales - (float) $r->costed_cost - (float) $r->costed_discount : null;

    return [
      'lines'      => (int) $r->line_count,
      'orders'     => (int) $r->orders,
      'products'   => (int) $r->products,
      'items'      => (int) $r->items,
      'sales'      => $sales,
      'discount'   => (float) $r->discount,
      'net'        => $sales - (float) $r->discount,
      'coverage'   => $sales > 0 ? $costedSales / $sales : 0, // share of sales that has a real cost
      'profit'     => $profit,                                  // on costed lines only, after their discount
      'margin'     => $profit !== null && $costedSales > 0 ? $profit / $costedSales : null,
    ];
  }

  /** One row per product: quantity, orders, sales, discount, share. */
  public function byProduct( array $f, string $sort = 'sales' ) : array
  {
    $rows = $this->withOrderTotals( $this->lines( $f ), $f )
                 ->groupBy( 'oi.product_id', 'p.name', 'c.name' )
                 ->selectRaw( 'oi.product_id AS id, p.name AS name, c.name AS category,
                     SUM(oi.stock) AS qty,
                     COUNT(DISTINCT oi.order_id) AS orders,
                     SUM(oi.stock * oi.sale_price) AS sales,
                     SUM(' . self::DISCOUNT_SHARE . ') AS discount,
                     MIN(oi.sale_price) AS min_price, MAX(oi.sale_price) AS max_price' )
                 ->orderByDesc( $sort === 'qty' ? 'qty' : 'sales' )
                 ->get();

    $total = (float) $rows->sum( 'sales' ) ?: 1;
    return $rows->map( fn( $r ) => [
      'id'        => (int) $r->id,
      'name'      => $r->name ?? ( 'Product #' . $r->id . ' (deleted)' ),
      'category'  => $r->category ?? 'Uncategorised',
      'qty'       => (int) $r->qty,
      'orders'    => (int) $r->orders,
      'sales'     => (float) $r->sales,
      'discount'  => (float) $r->discount,
      'net'       => (float) $r->sales - (float) $r->discount,
      'avg_price' => $r->qty > 0 ? (float) $r->sales / $r->qty : 0,
      'price'     => (float) $r->min_price === (float) $r->max_price
        ? number_format( (float) $r->min_price )
        : number_format( (float) $r->min_price ) . '–' . number_format( (float) $r->max_price ),
      'share'     => (float) $r->sales / $total,
    ] )->all();
  }

  /** Sales by category for the same filters (for the small breakdown). */
  public function byCategory( array $f ) : array
  {
    $rows = $this->lines( $f )
                 ->groupBy( 'c.name' )
                 ->selectRaw( 'c.name AS name, SUM(oi.stock) AS qty, SUM(oi.stock * oi.sale_price) AS sales' )
                 ->orderByDesc( 'sales' )->get();
    $total = (float) $rows->sum( 'sales' ) ?: 1;
    return $rows->map( fn( $r ) => [
      'name' => $r->name ?? 'Uncategorised', 'qty' => (int) $r->qty, 'sales' => (float) $r->sales, 'share' => $r->sales / $total,
    ] )->all();
  }

  public function categories() : array
  {
    return DB::table( 'categories' )->whereNull( 'deleted_at' )->orderBy( 'name' )->pluck( 'name', 'id' )->all();
  }

  /** Order lines for the Margin / Profit table (server-side paging). */
  public function linesTable( Request $request, DataTables $dataTables )
  {
    capDataTablesLength( $request );
    $f = $this->filters( $request, 'month' );

    $query = $this->withOrderTotals( $this->lines( $f ), $f )
                  ->select( 'oi.order_id', 'o.date', 'p.name AS product', 'c.name AS category', 'oi.stock', 'oi.sale_price', 'oi.purchase_price' )
                  ->selectRaw( 'oi.stock * oi.sale_price AS line_sales' )
                  ->selectRaw( '(' . self::DISCOUNT_SHARE . ') AS discount_share' )
                  ->selectRaw( '(' . self::COSTED . ') AS costed' )
                  ->orderByDesc( 'o.date' )->orderByDesc( 'oi.order_id' )->orderBy( 'oi.id' );

    $canView = user_can( 'access customer invoice' );
    $fmt = fn( $v ) => number_format( (float) $v, floor( (float) $v ) == (float) $v ? 0 : 2 );

    return $dataTables->query( $query )
                      ->filter( fn() => null )
                      ->editColumn( 'order_id', fn( $r ) => $canView
                        ? '<a href="' . route( 'order.invoice', $r->order_id ) . '" target="_blank">#' . $r->order_id . '</a>'
                        : '#' . $r->order_id )
                      ->editColumn( 'date', fn( $r ) => \Carbon\Carbon::parse( $r->date )->format( 'd-M-Y' ) )
                      ->editColumn( 'product', fn( $r ) => e( $r->product ?? 'Deleted product' )
                        . '<div class="rk-sub">' . e( $r->category ?? 'Uncategorised' ) . '</div>' )
                      ->editColumn( 'discount_share', fn( $r ) => (float) $r->discount_share > 0
                        ? $fmt( round( $r->discount_share, 2 ) ) : '<span class="rk-sub">0</span>' )
                      ->addColumn( 'net', fn( $r ) => $fmt( round( $r->line_sales - $r->discount_share, 2 ) ) )
                      ->addColumn( 'cost', fn( $r ) => $r->costed
                        ? $fmt( $r->stock * $r->purchase_price )
                        : '<span class="rk-sub" title="No cost price for this product">—</span>' )
                      ->addColumn( 'profit', function( $r ) use ( $fmt ) {
                        if( !$r->costed ) {
                          return '<span class="rk-sub">—</span>';
                        }
                        $p = round( $r->line_sales - $r->discount_share - $r->stock * $r->purchase_price, 2 );
                        return '<span class="' . ( $p < 0 ? 'rk-bad' : '' ) . '">' . $fmt( $p ) . '</span>';
                      } )
                      ->rawColumns( [ 'order_id', 'product', 'discount_share', 'cost', 'profit' ] )
                      ->make( true );
  }
}
