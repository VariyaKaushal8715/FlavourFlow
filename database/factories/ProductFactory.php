<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
        $name = fake()->unique()->words(3, true);

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(100, 9999),
            'sku' => fake()->unique()->bothify('FF-###??'),
            'category' => fake()->randomElement(['Pure spice', 'Signature blend', 'Everyday blend']),
            'unit' => fake()->randomElement(['50 g', '100 g', '200 g']),
            'description' => fake()->sentence(14),
            'long_description' => fake()->paragraphs(2, true),
            'highlights' => [
                'Freshly packed in small batches',
                'Balanced for everyday Indian cooking',
                'No artificial colours or fillers',
            ],
            'ingredients' => fake()->words(5, true),
            'usage_instructions' => fake()->sentence(12),
            'origin' => 'India',
            'badge' => fake()->randomElement(['New', 'Popular', 'Best seller']),
            'price' => fake()->randomFloat(2, 49, 499),
            'compare_at_price' => null,
            'quantity' => fake()->numberBetween(0, 250),
            'low_stock_threshold' => 10,
            'rating' => fake()->randomFloat(1, 3.5, 5),
            'priority' => fake()->numberBetween(1, 100),
            'image_path' => 'images/flavourflow-mark.png',
            'is_featured' => false,
            'is_active' => true,
        ];
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_featured' => true,
            'priority' => 100,
            'badge' => 'Hero pick',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    public function lowStock(): static
    {
        return $this->state(fn (array $attributes): array => [
            'quantity' => 3,
            'low_stock_threshold' => 5,
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes): array => [
            'quantity' => 0,
        ]);
    }
}
