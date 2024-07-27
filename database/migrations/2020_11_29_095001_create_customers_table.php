<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'customers', function( Blueprint $table ) {
            $table->id();
            $table->string( 'first_name' );
            $table->string( 'last_name' )->nullable( true );
            $table->string( 'business_name' )->nullable( true );
            $table->string( 'billing_address' )->nullable( true );
            $table->string( 'billing_city' )->nullable( true );
            $table->string( 'billing_state' )->nullable( true );
            $table->string( 'billing_zipcode' )->nullable( true );
            $table->string( 'billing_country' )->default( 0 );
            $table->string( 'shipping_address' )->nullable( true );
            $table->string( 'shipping_city' )->nullable( true );
            $table->string( 'shipping_state' )->nullable( true );
            $table->string( 'shipping_zipcode' )->nullable( true );
            $table->string( 'shipping_country' )->nullable( true );
            $table->string( 'phone_home' )->nullable( true );
            $table->string( 'phone_work' )->nullable( true );
            $table->string( 'phone_mobile' )->nullable( true );
            $table->string( 'phone_other' )->nullable( true );
            $table->string( 'email' )->nullable( true );
            $table->string( 'address' )->nullable( true );
            $table->enum( 'status', [ 'Active', 'Suspended' ] );
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
        Schema::dropIfExists( 'customers' );
    }
}
