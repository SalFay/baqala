<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\Order;
use App\Models\Vendor;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Dashboard figures for a date range and the range it is compared with.
 * Every figure is one SQL aggregate on the sale date (orders.date).
 */
class DashboardService
{
    /**
     * Cost is only known when the sale line recorded a real one: the POS has been
     * saving the sale price as the cost, and products have no purchase price yet.
     */
    private const COSTED = 'oi.purchase_price > 0 AND oi.purchase_price < oi.sale_price';

    /** Order hours from opening to after midnight (a sale at 01:00 belongs to the evening before). */
    private const HOURS = [ 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 0, 1, 2, 3, 4, 5 ];

    public function build( Carbon $from, Carbon $to, ?Carbon $cFrom, ?Carbon $cTo ) : array
    {
        $current = $this->summary( $from, $to );
        $compare = $cFrom ? $this->summary( $cFrom, $cTo ) : null;

        return [
            'current'    => $current,
            'compare'    => $compare,
            'daily'      => $this->daily( $from, $to, $cFrom, $cTo ),
            'hourly'     => $this->hourly( $from, $to ),
            'categories' => $this->categories( $from, $to, $cFrom, $cTo ),
            'products'   => $this->topProducts( $from, $to, $cFrom, $cTo ),
            'cashiers'   => $this->cashiers( $from, $to ),
            'months'     => $this->months( $to ),
            'expenses'   => $this->expenses( $from, $to ),
            'balances'   => $this->balances(),
        ];
    }

    public function summary( Carbon $from, Carbon $to ) : array
    {
        $o = DB::table( 'orders' )->whereNull( 'deleted_at' )->whereBetween( 'date', $this->dates( $from, $to ) )
               ->selectRaw( 'COUNT(*) AS orders,
                    COALESCE(SUM(sub_total), 0) AS item_sales,
                    COALESCE(SUM(' . Order::DISCOUNT_SQL . '), 0) AS discount,
                    COALESCE(SUM(delivery_charges), 0) AS delivery,
                    COALESCE(SUM(total), 0) AS net_sales,
                    COALESCE(SUM(debit), 0) AS unpaid' )
               ->first();

        $i = $this->lines( $from, $to )
                  ->selectRaw( 'COALESCE(SUM(oi.stock), 0) AS items,
                    COALESCE(SUM(CASE WHEN ' . self::COSTED . ' THEN oi.stock * oi.sale_price END), 0) AS costed_sales,
                    COALESCE(SUM(CASE WHEN ' . self::COSTED . ' THEN oi.stock * oi.purchase_price END), 0) AS costed_cost,
                    COALESCE(SUM(oi.stock * oi.sale_price), 0) AS line_sales' )
                  ->first();

        $expenses = (float) DB::table( 'accounts' )->whereNull( 'deleted_at' )
                              ->where( 'party_type', Expense::class )
                              ->whereBetween( 'created_at', $this->moments( $from, $to ) )
                              ->sum( 'debit' );

        $orders = (int) $o->orders;
        $coverage = $i->line_sales > 0 ? $i->costed_sales / $i->line_sales : 0;
        $margin = $i->costed_sales > 0 ? ( $i->costed_sales - $i->costed_cost ) / $i->costed_sales : null;

        return [
            'from'          => $from,
            'to'            => $to,
            'orders'        => $orders,
            'items'         => (int) $i->items,
            'item_sales'    => (float) $o->item_sales,
            'discount'      => (float) $o->discount,
            'delivery'      => (float) $o->delivery,
            'net_sales'     => (float) $o->net_sales,
            'unpaid'        => (float) $o->unpaid,
            'avg_order'     => $orders ? $o->net_sales / $orders : 0,
            'items_per_ord' => $orders ? $i->items / $orders : 0,
            'expenses'      => $expenses,
            // Profit only from lines with a real cost; coverage says how much of sales that is.
            'cost_coverage' => $coverage,
            'margin'        => $margin,
            'gross_profit'  => $coverage >= 0.999 ? $o->item_sales - $o->discount - $i->costed_cost : null,
            'net_profit'    => $coverage >= 0.999 ? $o->item_sales - $o->discount - $i->costed_cost - $expenses : null,
        ];
    }

    /** Net sales per day, with the comparison range lined up day by day. */
    private function daily( Carbon $from, Carbon $to, ?Carbon $cFrom, ?Carbon $cTo ) : array
    {
        $cur = $this->salesByDay( $from, $to );
        $cmp = $cFrom ? $this->salesByDay( $cFrom, $cTo ) : collect();

        $rows = [];
        $cDay = $cFrom ? $cFrom->copy() : null;
        foreach( CarbonPeriod::create( $from, $to ) as $day ) {
            $key = $day->toDateString();
            $rows[] = [
                'date'    => $key,
                'label'   => $day->format( 'd-M' ),
                'sales'   => (float) ( $cur[ $key ]->sales ?? 0 ),
                'orders'  => (int) ( $cur[ $key ]->orders ?? 0 ),
                'compare' => $cDay && $cDay->lte( $cTo ) ? (float) ( $cmp[ $cDay->toDateString() ]->sales ?? 0 ) : null,
            ];
            $cDay?->addDay();
        }
        return $rows;
    }

    private function salesByDay( Carbon $from, Carbon $to ) : Collection
    {
        return DB::table( 'orders' )->whereNull( 'deleted_at' )->whereBetween( 'date', $this->dates( $from, $to ) )
                 ->groupBy( 'date' )->selectRaw( 'date, SUM(total) AS sales, COUNT(*) AS orders' )
                 ->get()->keyBy( fn( $r ) => (string) $r->date );
    }

    /** Orders and sales by hour the order was rung up. */
    private function hourly( Carbon $from, Carbon $to ) : array
    {
        $rows = DB::table( 'orders' )->whereNull( 'deleted_at' )->whereBetween( 'date', $this->dates( $from, $to ) )
                  ->groupByRaw( 'HOUR(created_at)' )
                  ->selectRaw( 'HOUR(created_at) AS h, COUNT(*) AS orders, SUM(total) AS sales' )
                  ->get()->keyBy( 'h' );

        return collect( self::HOURS )
            ->map( fn( $h ) => [ 'hour' => $h, 'label' => Carbon::createFromTime( $h )->format( 'g A' ),
                                 'orders' => (int) ( $rows[ $h ]->orders ?? 0 ), 'sales' => (float) ( $rows[ $h ]->sales ?? 0 ) ] )
            ->filter( fn( $r ) => $r[ 'orders' ] > 0 || ( $r[ 'hour' ] >= 12 && $r[ 'hour' ] <= 23 ) )
            ->values()->all();
    }

    private function categories( Carbon $from, Carbon $to, ?Carbon $cFrom, ?Carbon $cTo ) : array
    {
        $cur = $this->salesByCategory( $from, $to );
        $cmp = $cFrom ? $this->salesByCategory( $cFrom, $cTo ) : collect();
        $total = $cur->sum( 'sales' ) ?: 1;

        return $cur->map( fn( $r ) => [
            'name'   => $r->name ?? 'Uncategorised',
            'items'  => (int) $r->items,
            'sales'  => (float) $r->sales,
            'share'  => $r->sales / $total,
            'change' => $this->change( $r->sales, $cmp[ $r->name ]->sales ?? null ),
        ] )->values()->all();
    }

    private function salesByCategory( Carbon $from, Carbon $to ) : Collection
    {
        return $this->lines( $from, $to )
                    ->leftJoin( 'products AS p', 'p.id', '=', 'oi.product_id' )
                    ->leftJoin( 'categories AS c', 'c.id', '=', 'p.category_id' )
                    ->groupBy( 'c.name' )
                    ->selectRaw( 'c.name AS name, SUM(oi.stock) AS items, SUM(oi.stock * oi.sale_price) AS sales' )
                    ->orderByDesc( 'sales' )
                    ->get()->keyBy( 'name' );
    }

    private function topProducts( Carbon $from, Carbon $to, ?Carbon $cFrom, ?Carbon $cTo, int $limit = 10 ) : array
    {
        $query = fn( Carbon $f, Carbon $t ) => $this->lines( $f, $t )
            ->leftJoin( 'products AS p', 'p.id', '=', 'oi.product_id' )
            ->groupBy( 'oi.product_id', 'p.name' )
            ->selectRaw( 'oi.product_id AS id, p.name AS name, SUM(oi.stock) AS qty, SUM(oi.stock * oi.sale_price) AS sales' );

        $cur = $query( $from, $to )->orderByDesc( 'sales' )->limit( $limit )->get();
        $cmp = $cFrom ? $query( $cFrom, $cTo )->whereIn( 'oi.product_id', $cur->pluck( 'id' ) )->get()->keyBy( 'id' ) : collect();
        $total = $this->lines( $from, $to )->sum( DB::raw( 'oi.stock * oi.sale_price' ) ) ?: 1;

        return $cur->map( fn( $r ) => [
            'name'   => $r->name ?? ( 'Product #' . $r->id ),
            'qty'    => (int) $r->qty,
            'sales'  => (float) $r->sales,
            'share'  => $r->sales / $total,
            'change' => $this->change( $r->sales, $cmp[ $r->id ]->sales ?? null ),
        ] )->all();
    }

    /** Cashier names are typed in by hand ("FAROOQ", "FarooQ.."), so group them loosely. */
    private function cashiers( Carbon $from, Carbon $to ) : array
    {
        $name = "NULLIF(REPLACE(UPPER(TRIM(COALESCE(cashier_name, ''))), '.', ''), '')";

        return DB::table( 'orders' )->whereNull( 'deleted_at' )->whereBetween( 'date', $this->dates( $from, $to ) )
                 ->groupByRaw( $name )
                 ->selectRaw( "$name AS name, COUNT(*) AS orders, SUM(total) AS sales" )
                 ->orderByDesc( 'sales' )->get()
                 ->map( fn( $r ) => [ 'name' => $r->name ?? 'Not entered', 'orders' => (int) $r->orders,
                                      'sales' => (float) $r->sales, 'avg' => $r->orders ? $r->sales / $r->orders : 0 ] )
                 ->all();
    }

    /** Last 12 months up to the end of the range: sales, orders and discounts. */
    private function months( Carbon $to ) : array
    {
        $start = $to->copy()->startOfMonth()->subMonths( 11 );
        $rows = DB::table( 'orders' )->whereNull( 'deleted_at' )
                  ->whereBetween( 'date', [ $start->toDateString(), $to->copy()->endOfMonth()->toDateString() ] )
                  ->groupByRaw( "DATE_FORMAT(date, '%Y-%m')" )
                  ->selectRaw( "DATE_FORMAT(date, '%Y-%m') AS ym, COUNT(*) AS orders, SUM(total) AS sales,
                        SUM(" . Order::DISCOUNT_SQL . ") AS discount, SUM(delivery_charges) AS delivery" )
                  ->get()->keyBy( 'ym' );

        $out = [];
        for( $m = $start->copy(); $m->lte( $to ); $m->addMonth() ) {
            $r = $rows[ $m->format( 'Y-m' ) ] ?? null;
            $orders = (int) ( $r->orders ?? 0 );
            $out[] = [
                'label'    => $m->format( 'M Y' ),
                'orders'   => $orders,
                'sales'    => (float) ( $r->sales ?? 0 ),
                'discount' => (float) ( $r->discount ?? 0 ),
                'delivery' => (float) ( $r->delivery ?? 0 ),
                'avg'      => $orders ? ( $r->sales / $orders ) : 0,
            ];
        }
        return $out;
    }

    private function expenses( Carbon $from, Carbon $to ) : array
    {
        return DB::table( 'expenses AS e' )
                 ->leftJoin( 'accounts AS a', function( $j ) use ( $from, $to ) {
                     $j->on( 'a.party_id', '=', 'e.id' )->where( 'a.party_type', Expense::class )
                       ->whereNull( 'a.deleted_at' )->whereBetween( 'a.created_at', $this->moments( $from, $to ) );
                 } )
                 ->whereNull( 'e.deleted_at' )
                 ->groupBy( 'e.id', 'e.name' )
                 ->selectRaw( 'e.name AS name, COALESCE(SUM(a.debit), 0) AS amount' )
                 ->orderByDesc( 'amount' )->get()
                 ->map( fn( $r ) => [ 'name' => $r->name, 'amount' => (float) $r->amount ] )->all();
    }

    /** Where the money is now (not limited to the date range). */
    private function balances() : array
    {
        $banks = DB::table( 'banks AS b' )
                   ->leftJoin( 'accounts AS a', fn( $j ) => $j->on( 'a.bank_id', '=', 'b.id' )->whereNull( 'a.deleted_at' ) )
                   ->whereNull( 'b.deleted_at' )->groupBy( 'b.id', 'b.name' )
                   ->selectRaw( 'b.name AS name, COALESCE(SUM(a.credit), 0) - COALESCE(SUM(a.debit), 0) AS amount' )
                   ->get()->map( fn( $r ) => [ 'name' => $r->name, 'amount' => (float) $r->amount ] )->all();

        $party = fn( string $type, string $sql ) => (float) DB::table( 'accounts' )->whereNull( 'deleted_at' )
                                                                ->where( 'party_type', $type )->sum( DB::raw( $sql ) );

        return [
            'banks'      => $banks,
            'receivable' => $party( Customer::class, 'COALESCE(debit, 0) - COALESCE(credit, 0)' ),
            'payable'    => $party( Vendor::class, 'COALESCE(credit, 0) - COALESCE(debit, 0)' ),
            'stock'      => (float) DB::table( 'inventory' )->whereNull( 'deleted_at' )->where( 'status', 'Available' )->sum( 'cost' ),
        ];
    }

    /** Sale lines of live orders in the range. */
    private function lines( Carbon $from, Carbon $to )
    {
        return DB::table( 'order_items AS oi' )
                 ->join( 'orders AS o', 'o.id', '=', 'oi.order_id' )
                 ->where( 'oi.order_type', Order::class )
                 ->whereNull( 'oi.deleted_at' )->whereNull( 'o.deleted_at' )
                 ->whereBetween( 'o.date', $this->dates( $from, $to ) );
    }

    public function change( $current, $previous ) : ?float
    {
        return $previous ? ( $current - $previous ) / abs( $previous ) : null;
    }

    private function dates( Carbon $from, Carbon $to ) : array
    {
        return [ $from->toDateString(), $to->toDateString() ];
    }

    private function moments( Carbon $from, Carbon $to ) : array
    {
        return [ $from->copy()->startOfDay()->toDateTimeString(), $to->copy()->endOfDay()->toDateTimeString() ];
    }
}
