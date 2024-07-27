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
        Vendor::create( [
            'name'    => 'Haroon Yousaf',
            'address' => 'Mingora',
            'mobile'   => '03339471086',
            'status'  => 'Active'
        ] );
    }
}
