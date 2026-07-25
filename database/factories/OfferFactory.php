<?php

namespace Database\Factories;

use App\Models\Offer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Offer>
 */
class OfferFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'eyebrow' => fake()->randomElement(['Today only', 'Season special', 'Member offer']),
            'title' => fake()->sentence(4),
            'description' => fake()->sentence(14),
            'discount_label' => fake()->randomElement(['Save 20%', 'Flat Rs. 100 off', 'Buy 2, get 1']),
            'coupon_code' => strtoupper(fake()->bothify('FLOW##')),
            'terms' => 'Valid while stocks last.',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addWeek(),
            'priority' => fake()->numberBetween(20, 90),
            'image_path' => 'images/flavourflow-spice-hero.png',
            'is_featured' => false,
            'is_active' => true,
        ];
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_featured' => true,
            'priority' => 100,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
}
