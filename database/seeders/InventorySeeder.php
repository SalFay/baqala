<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreInventory;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        // Get or create a default store
        $store = Store::first();

        if (!$store) {
            $store = Store::create([
                'name' => 'Main Store',
                'code' => 'MAIN',
                'address' => '123 Main Street',
                'phone' => '+1234567890',
                'is_active' => true,
            ]);
        }

        // Add inventory for all active products
        $products = Product::where('is_active', true)->get();

        foreach ($products as $product) {
            StoreInventory::updateOrCreate(
                [
                    'store_id' => $store->id,
                    'product_id' => $product->id,
                ],
                [
                    'quantity' => rand(10, 100),
                    'reserved_quantity' => 0,
                    'low_stock_threshold' => 5,
                ]
            );
        }

        $this->command->info('Inventory seeded for ' . $products->count() . ' products');
    }
}
