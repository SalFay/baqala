<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    class CreateUsersTable extends Migration
    {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
            Schema::create( 'users', static function( Blueprint $table ) {
                $table->id();
                $table->string( 'first_name' );
                $table->string( 'last_name' );
                $table->string( 'password' );
                $table->bigInteger( 'role_id' )->nullable();
                $table->string( 'email' )->unique();
                $table->text( 'phone' )->nullable();
                $table->tinyInteger( 'verified' )->nullable();
                $table->enum( 'status', [
                    'Active',
                    'Suspended'
                ] )->default( 'Suspended' );
                $table->rememberToken();
                $table->timestamps();
            } );
        }

        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public
        function down()
        {
            Schema::dropIfExists( 'users' );
        }
    }
