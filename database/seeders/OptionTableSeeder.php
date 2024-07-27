<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Option;

class OptionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Option::set( 'redirect.role.1', 'admin' );
        Option::set( 'role.admin', 1 );
        Option::set( 'title', 'Point of Sale' );
        Option::set( 'logo', 'logo.png' );
        Option::set( 'address', 'Mingora' );
        Option::set( 'phone', '03339471086' );
    }
}
