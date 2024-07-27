<?php

namespace Database\Seeders;

use App\Models\Product;
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
        Product::create( [
            'category_id'    => 1,
            'pid'            => 'SM-1',
            'name'           => 'Samsung Galaxy S9',
            'sale_price'     => 800,
            'purchase_price' => 698.88
        ] );
        Product::create( [
            'category_id'    => 1,
            'pid'            => 'SM-2',
            'name'           => 'Samsung Galaxy S8',
            'sale_price'     => 900,
            'purchase_price' => 750
        ] );
        Product::create( [
            'category_id'    => 1,
            'pid'            => 'SM-3',
            'name'           => 'Samsung Galaxy S6',
            'sale_price'     => 1000,
            'purchase_price' => 850
        ] );

    }
}
