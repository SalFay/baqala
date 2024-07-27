<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    class CreateVendorsTable extends Migration
    {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
            Schema::create( 'vendors', function( Blueprint $table ) {
                $table->id();
                $table->string( 'name' );
                $table->string( 'address' )->nullable( true );
                $table->bigInteger( 'mobile' )->nullable( true );
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
            Schema::dropIfExists( 'vendors' );
        }
    }