<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 5);
        $unitPrice = fake()->randomFloat(2, 10, 200);
        $costPrice = $unitPrice * 0.6;
        $taxRate = 15;
        $taxAmount = ($unitPrice * $quantity) * ($taxRate / 100);

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_variant_id' => null,
            'sku' => fake()->unique()->bothify('SKU-????-####'),
            'product_name' => fake()->words(3, true),
            'variant_name' => null,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'cost_price' => $costPrice,
            'discount' => 0,
            'discount_percent' => 0,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'line_total' => ($unitPrice * $quantity),
        ];
    }
}
