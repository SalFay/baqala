<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    class CreatePaymentsTable extends Migration
    {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
            Schema::create( 'payments', function( Blueprint $table ) {
                $table->id();
                $table->morphs( 'order' );
                $table->double( 'amount' );
                $table->unsignedBigInteger( 'payment_method_id' )->nullable( true );
                $table->date( 'scheduled_on' )->nullable( true );
                $table->date( 'completed_on' )->nullable( true );
                $table->enum( 'status', [ 'Scheduled', 'Refunded', 'Cancelled', 'Completed' ] )->default( 'Scheduled' );
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
            Schema::dropIfExists( 'payments' );
        }
    }
