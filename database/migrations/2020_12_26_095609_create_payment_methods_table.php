<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    class CreatePaymentMethodsTable extends Migration
    {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
            Schema::create( 'payment_methods', function( Blueprint $table ) {
                $table->id();
                $table->morphs( 'paymentable' );
                $table->string( 'name' )->nullable( true );
                $table->string( 'account_title' )->nullable( true );
                $table->string( 'account_branch' )->nullable( true );
                $table->string( 'account_number' )->nullable( true );
                $table->enum( 'source', [ 'Cash', 'Bank', 'Easypaisa', 'Jazzcash' ] );
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
            Schema::dropIfExists( 'payment_methods' );
        }
    }
