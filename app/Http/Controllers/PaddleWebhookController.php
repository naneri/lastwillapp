<?php

namespace App\Http\Controllers;

use App\Domain\Enums\Plan;
use App\Domain\Services\PlanLifecycle;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Receives Paddle's subscription webhooks and keeps the owner's local plan
 * (SPEC.md §12) in sync. Upgrades are applied immediately; downgrades are
 * recorded as a pending change and applied later by
 * PlanLifecycle::applyDuePlanChanges() once the current paid period ends.
 */
class PaddleWebhookController extends Controller
{
    public function handle(Request $request, PlanLifecycle $lifecycle): Response
    {
        if (! $this->hasValidSignature($request)) {
            abort(403, 'Invalid Paddle signature.');
        }

        $payload = $request->json()->all();
        $data = $payload['data'] ?? [];
        $owner = $this->resolveOwner($data);

        if (! $owner) {
            return response()->noContent();
        }

        match ($payload['event_type'] ?? null) {
            'subscription.created', 'subscription.activated' => $this->handleActivated($owner, $data, $lifecycle),
            'subscription.updated' => $this->handleUpdated($owner, $data, $lifecycle),
            'subscription.canceled' => $lifecycle->upgrade($owner, Plan::Free),
            default => null,
        };

        return response()->noContent();
    }

    protected function resolveOwner(array $data): ?User
    {
        $userId = $data['custom_data']['user_id'] ?? null;

        return $userId ? User::find($userId) : null;
    }

    protected function handleActivated(User $owner, array $data, PlanLifecycle $lifecycle): void
    {
        $plan = $this->planFromPriceId($data['items'][0]['price']['id'] ?? null);

        if (! $plan) {
            return;
        }

        if (isset($data['customer_id'])) {
            $owner->paddle_customer_id = $data['customer_id'];
            $owner->save();
        }

        $lifecycle->upgrade($owner, $plan);
    }

    protected function handleUpdated(User $owner, array $data, PlanLifecycle $lifecycle): void
    {
        $scheduledChange = $data['scheduled_change'] ?? null;

        if (! $scheduledChange) {
            return;
        }

        $targetPlan = $scheduledChange['action'] === 'cancel'
            ? Plan::Free
            : $this->planFromPriceId($data['items'][0]['price']['id'] ?? null);

        if (! $targetPlan) {
            return;
        }

        $lifecycle->scheduleDowngrade($owner, $targetPlan, CarbonImmutable::parse($scheduledChange['effective_at']));
    }

    protected function planFromPriceId(?string $priceId): ?Plan
    {
        if (! $priceId) {
            return null;
        }

        return match ($priceId) {
            config('paddle.prices.basic') => Plan::Basic,
            config('paddle.prices.pro') => Plan::Pro,
            default => null,
        };
    }

    protected function hasValidSignature(Request $request): bool
    {
        $secret = config('paddle.webhook_secret');

        if (! $secret) {
            return false;
        }

        $parts = [];
        foreach (explode(';', (string) $request->header('Paddle-Signature')) as $pair) {
            [$key, $value] = array_pad(explode('=', $pair, 2), 2, null);
            $parts[$key] = $value;
        }

        if (! isset($parts['ts'], $parts['h1'])) {
            return false;
        }

        $expected = hash_hmac('sha256', $parts['ts'].':'.$request->getContent(), $secret);

        return hash_equals($expected, $parts['h1']);
    }
}
