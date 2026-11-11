<?php

namespace Tests\Feature;

use App\Domain\Enums\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BillingControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_upgrading_an_existing_subscription_changes_its_price_instead_of_opening_a_new_checkout(): void
    {
        Config::set('paddle.api_key', 'test-key');
        Config::set('paddle.sandbox', true);
        Config::set('paddle.prices.pro', 'pri_pro_123');

        $owner = User::factory()->create(['plan' => Plan::Basic]);
        $owner->forceFill(['paddle_subscription_id' => 'sub_123'])->save();

        Http::fake([
            'sandbox-api.paddle.com/subscriptions/sub_123' => Http::response(['data' => ['id' => 'sub_123']], 200),
        ]);

        $response = $this->actingAs($owner)->post('/billing/upgrade/pro');

        $response->assertRedirect(route('billing.show'));
        $this->assertSame(Plan::Pro, $owner->fresh()->plan);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://sandbox-api.paddle.com/subscriptions/sub_123'
                && $request->method() === 'PATCH'
                && $request['items'][0]['price_id'] === 'pri_pro_123'
                && $request['proration_billing_mode'] === 'prorated_immediately';
        });
    }

    public function test_upgrade_without_an_existing_subscription_does_nothing_server_side(): void
    {
        Config::set('paddle.api_key', 'test-key');
        Config::set('paddle.prices.basic', 'pri_basic_123');

        // A Free owner has no paddle_subscription_id yet — they're meant
        // to go through the Paddle.js checkout overlay on the billing
        // page instead, not this route.
        $owner = User::factory()->create(['plan' => Plan::Free]);

        Http::fake();

        $response = $this->actingAs($owner)->post('/billing/upgrade/basic');

        $response->assertRedirect(route('billing.show'));
        $this->assertSame(Plan::Free, $owner->fresh()->plan);
        Http::assertNothingSent();
    }

    public function test_failed_paddle_update_does_not_change_the_local_plan(): void
    {
        Config::set('paddle.api_key', 'test-key');
        Config::set('paddle.prices.pro', 'pri_pro_123');

        $owner = User::factory()->create(['plan' => Plan::Basic]);
        $owner->forceFill(['paddle_subscription_id' => 'sub_123'])->save();

        Http::fake([
            'sandbox-api.paddle.com/*' => Http::response(['error' => ['code' => 'bad_request']], 400),
        ]);

        $response = $this->actingAs($owner)->post('/billing/upgrade/pro');

        $response->assertRedirect(route('billing.show'));
        $this->assertSame(Plan::Basic, $owner->fresh()->plan);
    }
}
