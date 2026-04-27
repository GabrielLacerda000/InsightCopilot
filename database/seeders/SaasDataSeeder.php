<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SaasDataSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::findOrFail(1);

        $company = Company::create([
            'user_id' => $user->id,
            'name' => 'Acme SaaS Inc.',
        ]);

        $plans = $this->createPlans($company);

        $this->simulateMonths($company, $plans);
    }

    /** @return array<string, SubscriptionPlan> */
    private function createPlans(Company $company): array
    {
        return [
            'starter' => SubscriptionPlan::create([
                'company_id' => $company->id,
                'name' => 'Starter',
                'price_monthly' => 29.00,
            ]),
            'pro' => SubscriptionPlan::create([
                'company_id' => $company->id,
                'name' => 'Pro',
                'price_monthly' => 79.00,
            ]),
            'enterprise' => SubscriptionPlan::create([
                'company_id' => $company->id,
                'name' => 'Enterprise',
                'price_monthly' => 199.00,
            ]),
        ];
    }

    /** @param array<string, SubscriptionPlan> $plans */
    private function simulateMonths(Company $company, array $plans): void
    {
        // New signups per month — gradual growth with one dip
        $newSignupsPerMonth = [30, 38, 45, 42, 55, 68, 72, 80, 88, 95, 110, 125];

        // Monthly churn rate per month (as a fraction of active subscriptions)
        $churnRatePerMonth = [0.06, 0.05, 0.05, 0.07, 0.04, 0.04, 0.03, 0.04, 0.03, 0.03, 0.02, 0.02];

        // Plan distribution weights: starter 60%, pro 30%, enterprise 10%
        $planWeights = ['starter' => 60, 'pro' => 30, 'enterprise' => 10];

        $now = Carbon::now();
        $startMonth = $now->copy()->subMonths(11)->startOfMonth();

        /** @var Subscription[] $activeSubscriptions */
        $activeSubscriptions = [];

        for ($i = 0; $i < 12; $i++) {
            $monthStart = $startMonth->copy()->addMonths($i);
            $monthEnd = $monthStart->copy()->endOfMonth();

            // New signups this month
            $newCount = $newSignupsPerMonth[$i];
            for ($j = 0; $j < $newCount; $j++) {
                $signupDate = $this->randomDateInMonth($monthStart, $monthEnd);

                $customer = Customer::create([
                    'company_id' => $company->id,
                    'name' => fake()->name(),
                    'email' => fake()->unique()->safeEmail(),
                    'cancelled_at' => null,
                ]);

                $planKey = $this->weightedRandom($planWeights);
                $plan = $plans[$planKey];

                $subscription = Subscription::create([
                    'customer_id' => $customer->id,
                    'plan_id' => $plan->id,
                    'started_at' => $signupDate,
                    'cancelled_at' => null,
                ]);

                // First invoice on signup date
                Invoice::create([
                    'customer_id' => $customer->id,
                    'subscription_id' => $subscription->id,
                    'amount' => $plan->price_monthly,
                    'status' => 'paid',
                    'paid_at' => $signupDate,
                ]);

                $activeSubscriptions[] = $subscription;
            }

            // Churn: cancel a portion of active subscriptions
            $churnCount = (int) round(count($activeSubscriptions) * $churnRatePerMonth[$i]);

            if ($churnCount > 0 && count($activeSubscriptions) > 0) {
                $churnCount = min($churnCount, count($activeSubscriptions));
                $churnIndexes = (array) array_rand($activeSubscriptions, $churnCount);

                foreach ($churnIndexes as $idx) {
                    $sub = $activeSubscriptions[$idx];
                    $cancelDate = $this->randomDateInMonth($monthStart, $monthEnd);

                    $sub->update(['cancelled_at' => $cancelDate]);
                    $sub->customer->update(['cancelled_at' => $cancelDate]);

                    unset($activeSubscriptions[$idx]);
                }

                $activeSubscriptions = array_values($activeSubscriptions);
            }

            // Monthly renewal invoices for subscriptions that started in a previous month
            $renewalDate = $monthStart->copy()->addDays(rand(1, 5));
            foreach ($activeSubscriptions as $sub) {
                $subStartedAt = Carbon::parse($sub->started_at);

                if ($subStartedAt->month === $monthStart->month && $subStartedAt->year === $monthStart->year) {
                    continue;
                }

                Invoice::create([
                    'customer_id' => $sub->customer_id,
                    'subscription_id' => $sub->id,
                    'amount' => $sub->plan->price_monthly,
                    'status' => 'paid',
                    'paid_at' => $renewalDate,
                ]);
            }
        }
    }

    private function randomDateInMonth(Carbon $start, Carbon $end): Carbon
    {
        return Carbon::createFromTimestamp(rand($start->timestamp, $end->timestamp));
    }

    /** @param array<string, int> $weights */
    private function weightedRandom(array $weights): string
    {
        $total = array_sum($weights);
        $rand = rand(1, $total);
        $cumulative = 0;

        foreach ($weights as $key => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $key;
            }
        }

        return array_key_first($weights);
    }
}
