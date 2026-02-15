<?php

namespace Database\Factories;

use App\Enums\Brand;
use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'brand' => fake()->randomElement(Brand::cases())->value,
            'type' => fake()->randomElement(EventType::cases())->value,
            'number_of_attendees' => fake()->numberBetween(10, 200),
            'location' => fake()->address(),
            'date' => fake()->dateTimeBetween('now', '+1 year')->format('Y-m-d'),
            'is_indoor' => fake()->boolean(),
            'status' => EventStatus::Pending->value,
            'notes' => fake()->optional(0.5)->sentence(),
        ];
    }

    /**
     * Indicate that the event is upcoming.
     */
    public function upcoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => fake()->dateTimeBetween('now', '+6 months')->format('Y-m-d'),
            'status' => fake()->randomElement([EventStatus::Pending, EventStatus::Paid, EventStatus::Confirmed])->value,
        ]);
    }

    /**
     * Indicate that the event is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'status' => EventStatus::Completed->value,
        ]);
    }

    /**
     * Indicate that the event is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EventStatus::Canceled->value,
        ]);
    }
}
