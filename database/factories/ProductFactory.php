<?php

namespace Database\Factories;

use App\Enums\ProductType;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $costPrice = fake()->randomFloat(2, 10, 500);
        $salePrice = $costPrice * fake()->randomFloat(2, 1.2, 2.0);

        return [
            'name' => fake()->words(3, true),
            'name_ar' => null,
            'sku' => strtoupper(fake()->unique()->bothify('PRD-????-####')),
            'barcode' => fake()->ean13(),
            'type' => ProductType::SIMPLE,
            'description' => fake()->paragraph(),
            'cost_price' => $costPrice,
            'sale_price' => $salePrice,
            'compare_price' => null,
            'track_inventory' => true,
            'low_stock_threshold' => 10,
            'is_active' => true,
        ];
    }

    public function variable(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => ProductType::VARIABLE,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withDiscount(): static
    {
        return $this->state(fn(array $attributes) => [
            'compare_price' => $attributes['sale_price'] * 1.3,
        ]);
    }

    public function noInventoryTracking(): static
    {
        return $this->state(fn(array $attributes) => [
            'track_inventory' => false,
        ]);
    }
}
