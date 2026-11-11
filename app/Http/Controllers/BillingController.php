<?php

namespace App\Http\Controllers;

use App\Domain\Enums\Plan;
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

        $base = config('paddle.sandbox') ? 'https://sandbox-api.paddle.com' : 'https://api.paddle.com';

        $response = Http::withToken($apiKey)
            ->post("{$base}/customers/{$owner->paddle_customer_id}/portal-sessions");

        $url = $response->json('data.urls.general.overview');

        if (! $response->successful() || ! $url) {
            return redirect()->route('billing.show')
                ->with('status', 'billing-portal-unavailable');
        }

        return redirect()->away($url);
    }
}
