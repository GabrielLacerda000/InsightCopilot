<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    public function definition(): array
    {
        $startedAt = fake()->dateTimeBetween('-12 months', 'now');

        return [
            'customer_id' => Customer::factory(),
            'plan_id' => SubscriptionPlan::factory(),
            'started_at' => $startedAt,
            'cancelled_at' => null,
        ];
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'cancelled_at' => fake()->dateTimeBetween($attributes['started_at'], 'now'),
        ]);
    }
}
