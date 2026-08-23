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
        $id = 'ORD-'.fake()->unique()->numerify('######');

        return [
            'order_id' => $id,
            'order_number' => $id,
            'user_id' => User::factory(),
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'cod',
            'name' => fake()->name(),
            'mobile' => fake()->numerify('##########'),
            'email' => fake()->safeEmail(),
            'address' => fake()->address(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'pincode' => '123456',
            'country' => 'India',
            'subtotal' => 500.00,
            'delivery_charge' => 0.00,
            'total' => 500.00,
            'total_amount' => 500.00,
        ];
    }
}
