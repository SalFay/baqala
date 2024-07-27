<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    class InventoryLog extends Migration
    {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
            Schema::create( 'inventory_log', function( Blueprint $table ) {
                $table->id();
                $table->morphs( 'order' );
                $table->unsignedBigInteger( 'product_id' );
                $table->bigInteger( 'stock' );
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
            Schema::dropIfExists( 'inventory_log' );
        }

    }
