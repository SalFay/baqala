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
        $customers = [
            [
                'first_name' => 'Walk-in',
                'last_name' => 'Customer',
                'phone' => '0000000000',
                'address' => 'N/A',
                'status' => 'Active',
            ],
            [
                'first_name' => 'Ahmed',
                'last_name' => 'Al-Rashid',
                'email' => 'ahmed@example.com',
                'phone' => '+966501234567',
                'address' => 'Riyadh, Saudi Arabia',
                'status' => 'Active',
            ],
            [
                'first_name' => 'Sara',
                'last_name' => 'Mohammed',
                'email' => 'sara@example.com',
                'phone' => '+966509876543',
                'address' => 'Jeddah, Saudi Arabia',
                'status' => 'Active',
            ],
        ];

        foreach ($customers as $customer) {
            Customer::firstOrCreate(
                ['phone' => $customer['phone']],
                $customer
            );
        }
    }
}
