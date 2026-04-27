<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'subscription_id' => Subscription::factory(),
            'amount' => fake()->randomElement([29.00, 79.00, 199.00]),
            'status' => 'paid',
            'paid_at' => fake()->dateTimeBetween('-12 months', 'now'),
        ];
    }

    public function refunded(): static
    {
        return $this->state(fn () => [
            'status' => 'refunded',
        ]);
    }
}
