<?php

namespace App\Http\Controllers;

use App\Domain\Enums\Plan;
use App\Domain\Services\PlanLifecycle;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

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
        $payload = $request->json()->all();
        $validSignature = $this->hasValidSignature($request);

        // Logged regardless of signature validity so a forged/misconfigured
        // request still shows up here rather than only in the 403 response.
        Log::channel('paddle')->info('Received Paddle webhook', [
            'event_id' => $payload['event_id'] ?? null,
            'event_type' => $payload['event_type'] ?? null,
            'valid_signature' => $validSignature,
            'payload' => $payload,
        ]);

        if (! $validSignature) {
            abort(403, 'Invalid Paddle signature.');
        }

        $data = $payload['data'] ?? [];
        $owner = $this->resolveOwner($data);

        if (! $owner) {
            return response('', 200);
        }

        match ($payload['event_type'] ?? null) {
            'subscription.created', 'subscription.activated' => $this->handleActivated($owner, $data, $lifecycle),
            'subscription.updated' => $this->handleUpdated($owner, $data, $lifecycle),
            'subscription.canceled' => $this->handleCanceled($owner, $lifecycle),
            default => null,
        };

        // Paddle's docs call for an exact 200 (not just any 2xx) to count the
        // delivery as successful — a 204 risks Paddle retrying deliveries we
        // already handled fine.
        return response('', 200);
    }

    protected function resolveOwner(array $data): ?User
    {
        $userId = $data['custom_data']['user_id'] ?? null;

        if ($userId) {
            return User::find($userId);
        }

        // Updates/cancellations on an existing subscription don't carry
        // custom_data — fall back to matching on the subscription ID we
        // stored when it was first created.
        $subscriptionId = $data['id'] ?? null;

        return $subscriptionId ? User::where('paddle_subscription_id', $subscriptionId)->first() : null;
    }

    protected function handleActivated(User $owner, array $data, PlanLifecycle $lifecycle): void
    {
        $plan = $this->planFromPriceId($data['items'][0]['price']['id'] ?? null);

        if (! $plan) {
            return;
        }

        $owner->paddle_customer_id = $data['customer_id'] ?? $owner->paddle_customer_id;
        $owner->paddle_subscription_id = $data['id'] ?? $owner->paddle_subscription_id;
        $owner->plan_renews_at = $this->nextBilledAt($data);
        $owner->save();

        $lifecycle->upgrade($owner, $plan);
    }

    protected function handleCanceled(User $owner, PlanLifecycle $lifecycle): void
    {
        $owner->paddle_subscription_id = null;
        $owner->plan_renews_at = null;
        $owner->save();

        $lifecycle->upgrade($owner, Plan::Free);
    }

    protected function handleUpdated(User $owner, array $data, PlanLifecycle $lifecycle): void
    {
        $scheduledChange = $data['scheduled_change'] ?? null;

        if ($scheduledChange) {
            $targetPlan = $scheduledChange['action'] === 'cancel'
                ? Plan::Free
                : $this->planFromPriceId($data['items'][0]['price']['id'] ?? null);

            if ($targetPlan) {
                $lifecycle->scheduleDowngrade($owner, $targetPlan, CarbonImmutable::parse($scheduledChange['effective_at']));

                // The subscription won't renew past the scheduled change, so
                // there's no future "renews on" date to show — the billing
                // page shows the scheduled change's effective date instead.
                $owner->plan_renews_at = null;
                $owner->save();
            }

            return;
        }

        // No scheduled change: an immediate item swap — e.g. the upgrade in
        // BillingController::upgrade(), or a change made directly in the
        // Paddle dashboard. Keep the local plan in sync either way.
        $plan = $this->planFromPriceId($data['items'][0]['price']['id'] ?? null);

        if ($plan) {
            $lifecycle->upgrade($owner, $plan);
            $owner->plan_renews_at = $this->nextBilledAt($data);
            $owner->save();
        }
    }

    protected function nextBilledAt(array $data): ?CarbonImmutable
    {
        return isset($data['next_billed_at']) ? CarbonImmutable::parse($data['next_billed_at']) : null;
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
