<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'inventory', function( Blueprint $table ) {
            $table->id();
            $table->unsignedBigInteger( 'stock_id' );
            $table->unsignedBigInteger( 'order_id' )->default( 0 )->nullable( true );
            $table->unsignedBigInteger( 'product_id' );
            $table->bigInteger( 'cost' );
            $table->enum( 'status', [ 'Available', 'Sold', 'Returned Vendor', 'Returned Order' ] );
            $table->date( 'date' );
            $table->timestamps();
            $table->softDeletes();
        } );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists( 'inventory' );
    }
}
