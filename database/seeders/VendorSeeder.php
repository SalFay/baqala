<?php

namespace Database\Seeders;

use App\Models\Vendor;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $vendors = [
            [
                'name' => 'Samsung Electronics',
                'code' => 'SAMSUNG',
                'contact_name' => 'John Smith',
                'email' => 'sales@samsung.com',
                'phone' => '+82-2-2053-3000',
                'address' => 'Seoul, South Korea',
                'is_active' => true,
            ],
            [
                'name' => 'Local Supplier',
                'code' => 'LOCAL01',
                'contact_name' => 'Mohammed Al-Fahad',
                'email' => 'supplier@local.sa',
                'phone' => '+966512345678',
                'address' => 'Riyadh Industrial Area',
                'is_active' => true,
            ],
            [
                'name' => 'Beverage Distributor',
                'code' => 'BEVDIST',
                'contact_name' => 'Ahmad Hassan',
                'email' => 'orders@bevdist.sa',
                'phone' => '+966567891234',
                'address' => 'Dammam, Saudi Arabia',
                'is_active' => true,
            ],
        ];

        foreach ($vendors as $vendor) {
            Vendor::firstOrCreate(
                ['code' => $vendor['code']],
                $vendor
            );
        }
    }
}
