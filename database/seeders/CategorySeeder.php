<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            ['name' => 'Electronics', 'code' => 'ELEC', 'description' => 'Electronic devices and accessories'],
            ['name' => 'Food & Beverages', 'code' => 'FOOD', 'description' => 'Food items and drinks'],
            ['name' => 'Clothing', 'code' => 'CLTH', 'description' => 'Apparel and accessories'],
            ['name' => 'Home & Garden', 'code' => 'HOME', 'description' => 'Home improvement and garden items'],
            ['name' => 'Health & Beauty', 'code' => 'HLTH', 'description' => 'Health and beauty products'],
        ];

        foreach ($categories as $i => $category) {
            Category::firstOrCreate(
                ['code' => $category['code']],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'sort_order' => $i,
                    'is_active' => true,
                ]
            );
        }
    }
}
