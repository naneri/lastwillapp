<?php

namespace App\Http\Controllers;

use App\Domain\Enums\Plan;
use App\Domain\Services\PlanLifecycle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function show(Request $request): View
    {
        $owner = $request->user();

        return view('billing.show', [
            'owner' => $owner,
            'plans' => Plan::cases(),
            'clientToken' => config('paddle.client_token'),
            'sandbox' => config('paddle.sandbox'),
            'prices' => config('paddle.prices'),
        ]);
    }

    /**
     * Upgrades an owner who's already on a paid plan to a higher one, by
     * changing the price on their existing Paddle subscription (prorated,
     * immediate — SPEC.md §12) rather than opening a new checkout, which
     * would create a second, parallel subscription instead of replacing
     * the first. First-time subscribers (no existing subscription) go
     * through the Paddle.js checkout overlay instead — see billing.show.
     */
    public function upgrade(Request $request, string $plan, PlanLifecycle $lifecycle): RedirectResponse
    {
        $owner = $request->user();
        $targetPlan = Plan::tryFrom($plan);
        $apiKey = config('paddle.api_key');
        $priceId = $targetPlan ? config("paddle.prices.{$targetPlan->value}") : null;

        if (! $targetPlan || ! $priceId || ! $apiKey || ! $owner->paddle_subscription_id) {
            return redirect()->route('billing.show')
                ->with('status', 'billing-not-configured');
        }

        $response = Http::withToken($apiKey)
            ->patch($this->apiBase()."/subscriptions/{$owner->paddle_subscription_id}", [
                'items' => [['price_id' => $priceId, 'quantity' => 1]],
                'proration_billing_mode' => 'prorated_immediately',
            ]);

        if (! $response->successful()) {
            return redirect()->route('billing.show')
                ->with('status', 'billing-upgrade-failed');
        }

        $lifecycle->upgrade($owner, $targetPlan);

        return redirect()->route('billing.show')->with('status', 'plan-updated');
    }

    /**
     * Polled by billing.show after a successful checkout: the overlay's
     * own "completed" event fires client-side before Paddle's webhook has
     * necessarily reached us, so the page can't just reload once and trust
     * what it sees — it reloads for real once this reports the new plan.
     */
    public function status(Request $request): JsonResponse
    {
        return response()->json(['plan' => $request->user()->plan->value]);
    }

    /**
     * Redirects to Paddle's hosted customer portal, where owners manage
     * downgrades/cancellations themselves (SPEC.md §12) — we don't build
     * that UI ourselves.
     */
    public function portal(Request $request): RedirectResponse
    {
        $owner = $request->user();
        $apiKey = config('paddle.api_key');

        if (! $apiKey || ! $owner->paddle_customer_id) {
            return redirect()->route('billing.show')
                ->with('status', 'billing-not-configured');
        }

        // Paddle requires a JSON object body, even an empty one — an empty
        // PHP array encodes to `[]`, not `{}`, and Paddle rejects that.
        $response = Http::withToken($apiKey)
            ->post($this->apiBase()."/customers/{$owner->paddle_customer_id}/portal-sessions", (object) []);

        $url = $response->json('data.urls.general.overview');

        if (! $response->successful() || ! $url) {
            return redirect()->route('billing.show')
                ->with('status', 'billing-portal-unavailable');
        }

        return redirect()->away($url);
    }

    protected function apiBase(): string
    {
        return config('paddle.sandbox') ? 'https://sandbox-api.paddle.com' : 'https://api.paddle.com';
    }
}
