<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'order_number' => 'ORD-'.fake()->unique()->numerify('##########'),
            'total_amount' => fake()->randomFloat(2, 50, 1000),
            'status' => 'pending',
            'payment_status' => 'pending',
        ];
    }
}
