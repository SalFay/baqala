<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'products', function( Blueprint $table ) {
            $table->id();
            $table->string( 'name' );
            $table->text( 'pid' );
            $table->unsignedBigInteger( 'category_id' );
            $table->bigInteger( 'purchase_price' );
            $table->bigInteger( 'sale_price' );
            $table->enum( 'status', [ 'Active', 'Suspended' ] );
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
        Schema::dropIfExists( 'products' );
    }
}
