<?php

namespace Database\Factories;

use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

class StoreFactory extends Factory
{
    protected $model = Store::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'code' => strtoupper(fake()->unique()->lexify('STR???')),
            'address' => fake()->address(),
            'city' => fake()->city(),
            'country' => 'Saudi Arabia',
            'phone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'tax_number' => fake()->numerify('###########'),
            'currency' => 'SAR',
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }
}
