<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => \App\Models\Product::factory(),
            'name' => fake()->words(2, true),
            'image' => null,
            'minimum_order_quantity' => fake()->randomElement([1, 5, 10, 20]),
            'increase_rate' => fake()->randomElement([1, 5, 10]),
            'maximum_available_quantity_per_day' => fake()->randomElement([null, 50, 100, 200]),
            'is_available' => true,
        ];
    }

    /**
     * Indicate that the variant is unavailable.
     */
    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_available' => false,
        ]);
    }
}
