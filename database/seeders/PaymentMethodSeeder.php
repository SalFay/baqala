<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
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
        $methods = [
            [
                'name' => 'Cash',
                'code' => 'CASH',
                'is_active' => true,
                'sort_order' => 0,
            ],
            [
                'name' => 'Credit Card',
                'code' => 'CARD',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Mada',
                'code' => 'MADA',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Bank Transfer',
                'code' => 'BANK',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Store Credit',
                'code' => 'CREDIT',
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::firstOrCreate(
                ['code' => $method['code']],
                $method
            );
        }
    }
}
