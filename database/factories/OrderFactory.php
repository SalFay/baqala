<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 50, 1000);
        $taxAmount = $subtotal * 0.15;
        $discount = fake()->optional(0.2)->randomFloat(2, 0, $subtotal * 0.1) ?? 0;

        return [
            'order_number' => 'ORD' . fake()->unique()->numerify('########'),
            'invoice_no' => 'INV' . date('Y') . fake()->unique()->numerify('######'),
            'store_id' => Store::factory(),
            'customer_id' => null,
            'user_id' => User::factory(),
            'status' => OrderStatus::COMPLETED,
            'payment_status' => PaymentStatus::PAID,
            'payment_type' => fake()->randomElement(['cash', 'card']),
            'sub_total' => $subtotal,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount' => $discount,
            'total' => $subtotal + $taxAmount - $discount,
            'customer_name' => fake()->optional()->name(),
            'cashier_name' => fake()->name(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => OrderStatus::PENDING,
            'payment_status' => PaymentStatus::PENDING,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => OrderStatus::CANCELLED,
            'payment_status' => PaymentStatus::CANCELLED,
        ]);
    }

    public function withCustomer(): static
    {
        return $this->state(fn(array $attributes) => [
            'customer_id' => Customer::factory(),
        ]);
    }
}
