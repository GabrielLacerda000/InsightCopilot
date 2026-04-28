<?php

use App\Ai\Agents\AnalyticsInsightAgent;
use App\Ai\Agents\SqlGeneratorAgent;
use App\Ai\Tools\RunQueryTool;
use App\Models\Company;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Tools\Request;

uses(RefreshDatabase::class);

it('redirects unauthenticated users from analytics index', function () {
    $this->get(route('analytics.index'))->assertRedirect(route('login'));
});

it('renders analytics index for authenticated users', function () {
    $user = User::factory()->create();

    $this->withoutVite()
        ->actingAs($user)
        ->get(route('analytics.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Analytics/Index'));
});

it('redirects unauthenticated users from analytics ask', function () {
    $this->postJson(route('analytics.ask'), ['question' => 'test'])->assertUnauthorized();
});

it('returns sql, data and text for authenticated ask request', function () {
    SqlGeneratorAgent::fake(['Query executed.']);
    AnalyticsInsightAgent::fake(['MRR increased 21% over the last 6 months while churn decreased by 8%.']);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('analytics.ask'), ['question' => 'Show MRR by month'])
        ->assertOk()
        ->assertJsonStructure(['sql', 'data', 'text'])
        ->assertJsonPath('text', 'MRR increased 21% over the last 6 months while churn decreased by 8%.');

    SqlGeneratorAgent::assertPrompted('Show MRR by month');
    AnalyticsInsightAgent::assertPrompted(fn ($prompt) => str_contains($prompt->prompt, 'Show MRR by month'));
});

it('validates question is required on ask', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('analytics.ask'), [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['question']);
});

// RunQueryTool unit tests

it('executes a safe select query and returns rows', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create(['user_id' => $user->id]);
    Customer::factory()->create(['company_id' => $company->id, 'email' => 'alice@example.com']);

    $tool = new RunQueryTool;
    $tool->handle(new Request(['sql' => 'SELECT id, email FROM customers LIMIT 1']));

    expect($tool->lastData)->toBeArray()->toHaveCount(1)
        ->and($tool->lastData[0])->toHaveKeys(['id', 'email'])
        ->and($tool->lastSql)->toContain('customers');
});

it('returns error json for forbidden sql statements', function (string $sql) {
    $tool = new RunQueryTool;
    $result = json_decode(
        $tool->handle(new Request(['sql' => $sql])),
        true
    );

    expect($result)->toHaveKey('error')
        ->and($result['error'])->toBe('Forbidden SQL statement');
})->with([
    'UPDATE customers SET email = "x" WHERE 1=1',
    'DELETE FROM customers',
    'INSERT INTO customers (email) VALUES ("x")',
    'DROP TABLE customers',
]);

it('appends limit when query has none and stores results', function () {
    $tool = new RunQueryTool;
    $tool->handle(new Request(['sql' => 'SELECT COUNT(*) as total FROM customers']));

    expect($tool->lastSql)->toContain('LIMIT')
        ->and($tool->lastData)->toBeArray();
});
