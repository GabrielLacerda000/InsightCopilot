<?php

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

it('returns empty sql and data for authenticated ask request', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('analytics.ask'), ['question' => 'Show MRR by month'])
        ->assertOk()
        ->assertJson(['sql' => '', 'data' => []]);
});

it('validates question is required on ask', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('analytics.ask'), [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['question']);
});
