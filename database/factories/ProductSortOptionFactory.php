<?php

namespace Database\Factories;

use App\Models\ProductSortOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductSortOption>
 */
class ProductSortOptionFactory extends Factory
{
    protected $model = ProductSortOption::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(2),
            'label' => fake()->words(2, true),
            'sort_field' => 'price',
            'sort_direction' => fake()->randomElement(['asc', 'desc']),
            'display_order' => fake()->numberBetween(1, 100),
            'is_active' => true,
            'is_default' => false,
            'is_system' => false,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
            'is_active' => true,
        ]);
    }
}
