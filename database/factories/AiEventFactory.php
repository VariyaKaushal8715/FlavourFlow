<?php

namespace Database\Factories;

use App\AI\Models\AiEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiEvent>
 */
class AiEventFactory extends Factory
{
    protected $model = AiEvent::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_type' => 'product_viewed',
            'user_id' => User::factory(),
            'session_id' => fake()->uuid(),
            'entity_type' => 'product',
            'entity_id' => (string) fake()->numberBetween(1, 100),
            'metadata' => [
                'name' => fake()->words(2, true),
                'category' => 'Whole Spices',
            ],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
