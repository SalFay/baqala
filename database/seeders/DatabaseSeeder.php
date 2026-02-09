<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Core system data (stores, settings, chart of accounts, etc.)
        $this->call(InitialDataSeeder::class);

        // Application data
        $this->call(OptionTableSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(UserTableSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(ProductsSeeder::class);
        $this->call(CustomerSeeder::class);
        $this->call(VendorSeeder::class);
        $this->call(PaymentMethodSeeder::class);
    }
}
