<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Customer::create( [
            'first_name'      => 'Walking',
            'last_name'       => 'Customer',
            'phone_work'      => '0000000000',
            'billing_address' => 'No Address',
            'status'  => 'Active'
        ] );
    }
}
