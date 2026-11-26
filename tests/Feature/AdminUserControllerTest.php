<?php

namespace Tests\Feature;

use App\Domain\Enums\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_the_users_list(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        User::factory()->create(['plan' => Plan::Pro]);

        $response = $this->actingAs($admin)->get('/users');

        $response->assertOk();
        $response->assertSee($admin->email);
        $response->assertSee('Pro');
    }

    public function test_non_admin_cannot_view_the_users_list(): void
    {
        $owner = User::factory()->create(['is_admin' => false]);

        $this->actingAs($owner)->get('/users')->assertForbidden();
    }

    public function test_users_list_is_paginated(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        User::factory()->count(25)->create();

        $response = $this->actingAs($admin)->get('/users');

        $response->assertOk();
        $response->assertViewHas('users', fn ($users) => $users->count() === 20 && $users->hasMorePages());
    }
}
