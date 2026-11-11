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
                'customer_id' => 'ctm_123',
                'custom_data' => ['user_id' => $owner->id],
                'items' => [['price' => ['id' => 'pri_basic_123']]],
            ],
        ], 'test-secret');

        $response->assertNoContent();

        $owner->refresh();
        $this->assertSame(Plan::Basic, $owner->plan);
        $this->assertSame('ctm_123', $owner->paddle_customer_id);
    }

    public function test_subscription_updated_with_scheduled_cancel_schedules_a_downgrade(): void
    {
        Config::set('paddle.webhook_secret', 'test-secret');

        $owner = User::factory()->create(['plan' => Plan::Pro]);

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

        $response->assertNoContent();

        $owner->refresh();
        $this->assertSame(Plan::Pro, $owner->plan, 'stays on current plan until the effective date');
        $this->assertSame(Plan::Free, $owner->pending_plan);
        $this->assertNotNull($owner->plan_change_effective_at);
    }
}
