<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'TRX-'.fake()->unique()->numerify('########'),
            'customer_id' => Customer::factory(),
            'transaction_date' => fake()->dateTimeBetween('-30 days')->format('Y-m-d'),
            'total' => 0,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
