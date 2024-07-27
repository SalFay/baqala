<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\PaymentMethods;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PaymentMethods::create( [
            'paymentable_type' => Customer::class,
            'paymentable_id'   => 1,
            'name'             => 'Meezan Bank',
            'account_title'    => 'XpertzDev IT Solution',
            'account_number'   => '1802010000000',
            'source'           => 'Bank'
        ] );
        PaymentMethods::create( [
            'paymentable_type' => Vendor::class,
            'paymentable_id'   => 1,
            'name'             => 'Meezan Bank',
            'account_title'    => 'XpertzDev IT Solution',
            'account_number'   => '1802010000000',
            'source'           => 'Bank'
        ] );
    }
}
