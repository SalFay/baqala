<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'business_name' => fake()->optional(0.3)->company(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'city' => fake()->city(),
            'country' => 'Saudi Arabia',
            'loyalty_card_number' => 'LC' . fake()->unique()->numerify('########'),
            'credit_limit' => fake()->randomFloat(2, 0, 5000),
            'credit_balance' => 0,
            'status' => 'Active',
        ];
    }

    public function suspended(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'Suspended',
        ]);
    }

    public function withCredit(): static
    {
        return $this->state(fn(array $attributes) => [
            'credit_balance' => fake()->randomFloat(2, 100, 1000),
        ]);
    }
}
