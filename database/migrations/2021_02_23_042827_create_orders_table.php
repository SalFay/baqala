<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'orders', function( Blueprint $table ) {
            $table->id();
            $table->unsignedBigInteger( 'customer_id' );
            $table->enum( 'payment_type', [ 'Simple', 'Installment' ] )->default( 'Simple' );
            $table->string( 'sub_total' )->nullable( true );
            $table->string( 'discount' )->nullable( true );
            $table->string( 'delivery_charges' )->nullable( true );
            $table->string( 'total' )->nullable( true );
            $table->date( 'date' );
            $table->softDeletes();
            $table->timestamps();
        } );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists( 'orders' );
    }
}
