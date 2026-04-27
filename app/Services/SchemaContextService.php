<?php

namespace App\Services;

class SchemaContextService
{
    public function describe(): string
    {
        return <<<'TEXT'
        Tables:
        companies(id, user_id, name, created_at)
        customers(id, company_id, name, email, cancelled_at, created_at)
        subscription_plans(id, company_id, name, price_monthly, created_at)
        subscriptions(id, customer_id, plan_id, started_at, cancelled_at, created_at)
        invoices(id, customer_id, subscription_id, amount, status, paid_at, created_at)

        Relationships:
        - companies.user_id → users.id
        - customers.company_id → companies.id
        - subscription_plans.company_id → companies.id
        - subscriptions.customer_id → customers.id
        - subscriptions.plan_id → subscription_plans.id
        - invoices.customer_id → customers.id
        - invoices.subscription_id → subscriptions.id

        Notes:
        - invoices.status is either 'paid' or 'refunded'
        - customers.cancelled_at and subscriptions.cancelled_at are NULL when active
        - MRR (Monthly Recurring Revenue) is the sum of invoice amounts grouped by month
        TEXT;
    }
}
