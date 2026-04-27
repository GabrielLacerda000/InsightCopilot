<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'cancelled_at' => null,
        ];
    }

    public function churned(): static
    {
        return $this->state(fn () => [
            'cancelled_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ]);
    }
}
