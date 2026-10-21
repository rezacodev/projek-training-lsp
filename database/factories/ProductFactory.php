<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sku' => fake()->unique()->bothify('PRD-####'),
            'name' => fake()->words(3, true),
            'category' => fake()->randomElement(['Elektronik', 'Pakaian', 'Perlengkapan']),
            'price' => fake()->randomFloat(2, 10000, 5000000),
            'stock' => fake()->numberBetween(0, 250),
            'description' => fake()->sentence(),
        ];
    }
}
