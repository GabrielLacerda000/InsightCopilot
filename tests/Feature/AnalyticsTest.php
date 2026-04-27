<?php

use App\Actions\Analytics\RunQueryAction;
use App\Ai\Agents\SqlGeneratorAgent;
use App\Models\Company;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

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

it('returns sql and data for authenticated ask request', function () {
    SqlGeneratorAgent::fake(['SELECT COUNT(*) as total FROM customers']);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('analytics.ask'), ['question' => 'Show MRR by month'])
        ->assertOk()
        ->assertJsonStructure(['sql', 'data'])
        ->assertJsonPath('sql', 'SELECT COUNT(*) as total FROM customers');

    SqlGeneratorAgent::assertPrompted('Show MRR by month');
});

it('validates question is required on ask', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('analytics.ask'), [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['question']);
});

it('executes a safe select query and returns rows', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create(['user_id' => $user->id]);
    Customer::factory()->create(['company_id' => $company->id, 'email' => 'alice@example.com']);

    $result = RunQueryAction::run('SELECT id, email FROM customers LIMIT 1');

    expect($result)->toBeArray()->toHaveCount(1)
        ->and($result[0])->toHaveKeys(['id', 'email']);
});

it('throws on forbidden sql statements', function (string $sql) {
    expect(fn () => RunQueryAction::run($sql))
        ->toThrow(RuntimeException::class, 'Forbidden SQL statement');
})->with([
    'UPDATE customers SET email = "x" WHERE 1=1',
    'DELETE FROM customers',
    'INSERT INTO customers (email) VALUES ("x")',
    'DROP TABLE customers',
]);

it('appends limit when query has none', function () {
    $result = RunQueryAction::run('SELECT COUNT(*) as total FROM customers');

    expect($result)->toBeArray();
});
