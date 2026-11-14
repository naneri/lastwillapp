<?php

namespace Tests\Feature;

use App\Domain\Enums\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class PaddleWebhookControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function signedPost(array $payload, string $secret): \Illuminate\Testing\TestResponse
    {
        $body = json_encode($payload);
        $ts = (string) time();
        $signature = hash_hmac('sha256', $ts.':'.$body, $secret);

        return $this->call(
            'POST',
            '/webhooks/paddle',
            server: ['HTTP_Paddle-Signature' => "ts={$ts};h1={$signature}"],
            content: $body,
        );
    }

    public function test_webhook_with_invalid_signature_is_rejected(): void
    {
        Config::set('paddle.webhook_secret', 'test-secret');

        $response = $this->call(
            'POST',
            '/webhooks/paddle',
            server: ['HTTP_Paddle-Signature' => 'ts=123;h1=not-a-real-signature'],
            content: json_encode(['event_type' => 'subscription.created', 'data' => []]),
        );

        $response->assertForbidden();
    }

    public function test_valid_subscription_created_webhook_upgrades_owner_immediately(): void
    {
        Config::set('paddle.webhook_secret', 'test-secret');
        Config::set('paddle.prices.basic', 'pri_basic_123');

        $owner = User::factory()->create(['plan' => Plan::Free]);

        $response = $this->signedPost([
            'event_type' => 'subscription.created',
            'data' => [
                'id' => 'sub_123',
                'customer_id' => 'ctm_123',
                'custom_data' => ['user_id' => $owner->id],
                'items' => [['price' => ['id' => 'pri_basic_123']]],
                'next_billed_at' => '2027-01-15T00:00:00Z',
            ],
        ], 'test-secret');

        $response->assertOk();

        $owner->refresh();
        $this->assertSame(Plan::Basic, $owner->plan);
        $this->assertSame('ctm_123', $owner->paddle_customer_id);
        $this->assertSame('sub_123', $owner->paddle_subscription_id);
        $this->assertSame('2027-01-15', $owner->plan_renews_at->toDateString());
    }

    public function test_subscription_updated_with_no_scheduled_change_syncs_plan_immediately(): void
    {
        Config::set('paddle.webhook_secret', 'test-secret');
        Config::set('paddle.prices.pro', 'pri_pro_123');

        // No custom_data on this event — resolution must fall back to
        // matching the stored subscription ID, same as Paddle sends for an
        // update to an existing subscription (e.g. our own upgrade() call).
        $owner = User::factory()->create(['plan' => Plan::Basic]);
        $owner->forceFill(['paddle_subscription_id' => 'sub_123'])->save();

        $response = $this->signedPost([
            'event_type' => 'subscription.updated',
            'data' => [
                'id' => 'sub_123',
                'items' => [['price' => ['id' => 'pri_pro_123']]],
                'next_billed_at' => '2027-03-01T00:00:00Z',
            ],
        ], 'test-secret');

        $response->assertOk();

        $owner->refresh();
        $this->assertSame(Plan::Pro, $owner->plan);
        $this->assertSame('2027-03-01', $owner->plan_renews_at->toDateString());
    }

    public function test_subscription_canceled_clears_subscription_id_and_reverts_to_free(): void
    {
        Config::set('paddle.webhook_secret', 'test-secret');

        $owner = User::factory()->create(['plan' => Plan::Basic]);
        $owner->forceFill(['paddle_subscription_id' => 'sub_123', 'plan_renews_at' => now()->addMonth()])->save();

        $response = $this->signedPost([
            'event_type' => 'subscription.canceled',
            'data' => ['id' => 'sub_123'],
        ], 'test-secret');

        $response->assertOk();

        $owner->refresh();
        $this->assertSame(Plan::Free, $owner->plan);
        $this->assertNull($owner->paddle_subscription_id);
        $this->assertNull($owner->plan_renews_at);
    }

    public function test_subscription_updated_with_scheduled_cancel_schedules_a_downgrade(): void
    {
        Config::set('paddle.webhook_secret', 'test-secret');

        $owner = User::factory()->create(['plan' => Plan::Pro]);
        $owner->forceFill(['plan_renews_at' => now()->addMonth()])->save();

        $response = $this->signedPost([
            'event_type' => 'subscription.updated',
            'data' => [
                'custom_data' => ['user_id' => $owner->id],
                'scheduled_change' => [
                    'action' => 'cancel',
                    'effective_at' => now()->addDays(20)->toIso8601String(),
                ],
            ],
        ], 'test-secret');

        $response->assertOk();

        $owner->refresh();
        $this->assertSame(Plan::Pro, $owner->plan, 'stays on current plan until the effective date');
        $this->assertSame(Plan::Free, $owner->pending_plan);
        $this->assertNotNull($owner->plan_change_effective_at);
        $this->assertNull($owner->plan_renews_at, 'no future renewal once a cancellation is scheduled');
    }
}
