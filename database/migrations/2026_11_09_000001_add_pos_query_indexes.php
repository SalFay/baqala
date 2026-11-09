<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Indexes for the lookups that were full table scans:
 * - every unit sold: inventory where product_id = ? and status = 'Available'
 * - order edit/delete: inventory where order_id = ?
 * - dashboard and reports: orders by sale date
 *
 * Run on its own (the live migrations table does not list older migrations):
 *   php artisan migrate --path=database/migrations/2026_11_09_000001_add_pos_query_indexes.php --force
 */
class AddPosQueryIndexes extends Migration
{
    private const INDEXES = [
        'inventory' => [
            'inventory_product_id_status_index' => [ 'product_id', 'status' ],
            'inventory_order_id_index'          => [ 'order_id' ],
        ],
        'orders'    => [
            'orders_date_index' => [ 'date' ],
        ],
    ];

    public function up()
    {
        foreach( self::INDEXES as $table => $indexes ) {
            foreach( $indexes as $name => $columns ) {
                if( !$this->hasIndex( $table, $name ) ) {
                    Schema::table( $table, fn( Blueprint $t ) => $t->index( $columns, $name ) );
                }
            }
        }
    }

    public function down()
    {
        foreach( self::INDEXES as $table => $indexes ) {
            foreach( array_keys( $indexes ) as $name ) {
                if( $this->hasIndex( $table, $name ) ) {
                    Schema::table( $table, fn( Blueprint $t ) => $t->dropIndex( $name ) );
                }
            }
        }
    }

    private function hasIndex( string $table, string $name ) : bool
    {
        return DB::table( 'information_schema.statistics' )
                 ->where( 'table_schema', DB::getDatabaseName() )
                 ->where( 'table_name', $table )
                 ->where( 'index_name', $name )
                 ->exists();
    }
}
