<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Seeder;

class ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $store = Store::first();
        $electronics = Category::where('code', 'ELEC')->first();
        $food = Category::where('code', 'FOOD')->first();

        $products = [
            [
                'category_id' => $electronics?->id,
                'sku' => 'SM-S9-001',
                'barcode' => '8806088891026',
                'name' => 'Samsung Galaxy S9',
                'sale_price' => 800,
                'cost_price' => 698.88,
                'low_stock_threshold' => 5,
            ],
            [
                'category_id' => $electronics?->id,
                'sku' => 'SM-S8-001',
                'barcode' => '8806088777123',
                'name' => 'Samsung Galaxy S8',
                'sale_price' => 600,
                'cost_price' => 500,
                'low_stock_threshold' => 5,
            ],
            [
                'category_id' => $food?->id,
                'sku' => 'COK-355-001',
                'barcode' => '5000112583793',
                'name' => 'Coca-Cola 355ml',
                'sale_price' => 2.50,
                'cost_price' => 1.50,
                'low_stock_threshold' => 20,
            ],
            [
                'category_id' => $food?->id,
                'sku' => 'PEP-355-001',
                'barcode' => '4060800001016',
                'name' => 'Pepsi 355ml',
                'sale_price' => 2.50,
                'cost_price' => 1.50,
                'low_stock_threshold' => 20,
            ],
            [
                'category_id' => $food?->id,
                'sku' => 'CHIP-LAY-001',
                'barcode' => '028400083119',
                'name' => 'Lays Classic Chips',
                'sale_price' => 5.00,
                'cost_price' => 3.00,
                'low_stock_threshold' => 15,
            ],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(
                ['sku' => $product['sku']],
                array_merge($product, [
                    'store_id' => $store?->id,
                    'is_active' => true,
                    'track_inventory' => true,
                ])
            );
        }
    }
}
