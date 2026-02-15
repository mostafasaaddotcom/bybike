<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VariantPriceTier>
 */
class VariantPriceTierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $from = fake()->numberBetween(1, 50);
        $to = fake()->optional()->numberBetween($from + 10, $from + 100);

        return [
            'variant_id' => \App\Models\ProductVariant::factory(),
            'quantity_from' => $from,
            'quantity_to' => $to,
            'price' => fake()->randomFloat(2, 5, 500),
        ];
    }
}
