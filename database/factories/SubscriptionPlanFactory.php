<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubscriptionPlan>
 */
class SubscriptionPlanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => fake()->randomElement(['Starter', 'Pro', 'Enterprise']),
            'price_monthly' => fake()->randomElement([29.00, 79.00, 199.00]),
        ];
    }
}
