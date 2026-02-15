<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Event;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 100, 5000);
        $taxRate = fake()->randomFloat(2, 0, 15);
        $taxAmount = ($subtotal * $taxRate) / 100;
        $discountAmount = fake()->randomFloat(2, 0, 100);
        $total = $subtotal + $taxAmount - $discountAmount;

        $issuedAt = fake()->dateTimeBetween('-6 months', 'now');

        return [
            'event_id' => Event::factory(),
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'status' => fake()->randomElement(InvoiceStatus::cases())->value,
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'total' => $total,
            'notes' => fake()->optional(0.3)->sentence(),
            'issued_at' => $issuedAt,
            'due_at' => fake()->optional(0.7)->dateTimeBetween($issuedAt, '+30 days'),
            'paid_at' => fake()->optional(0.4)->dateTimeBetween($issuedAt, 'now'),
        ];
    }

    /**
     * Indicate that the invoice is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InvoiceStatus::Paid->value,
            'paid_at' => fake()->dateTimeBetween($attributes['issued_at'], 'now'),
        ]);
    }

    /**
     * Indicate that the invoice is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InvoiceStatus::Pending->value,
            'paid_at' => null,
        ]);
    }

    /**
     * Indicate that the invoice is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InvoiceStatus::Draft->value,
            'paid_at' => null,
        ]);
    }
}
