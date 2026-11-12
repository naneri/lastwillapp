<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">{{ __('Plan & billing') }}</h2>
    </x-slot>

    @if (session('status') === 'billing-not-configured')
        <div class="alert alert-warning">Billing isn't set up on this server yet.</div>
    @elseif (session('status') === 'billing-portal-unavailable')
        <div class="alert alert-danger">Couldn't reach the billing portal right now — please try again shortly.</div>
    @elseif (session('status') === 'billing-upgrade-failed')
        <div class="alert alert-danger">Couldn't update your plan right now — please try again shortly.</div>
    @elseif (session('status') === 'plan-updated')
        <div class="alert alert-success">Your plan has been updated.</div>
    @endif

    @if ($owner->plan === \App\Domain\Enums\Plan::Free)
        <div class="alert alert-info">
            You're on the free plan until
            <strong>{{ $owner->freeTierEndsAt()->toFormattedDateString() }}</strong>.
            Subscribe any time to keep monitoring running after that.
        </div>
    @elseif ($owner->pending_plan)
        <div class="alert alert-info">
            Your plan changes to <strong>{{ $owner->pending_plan->label() }}</strong>
            on {{ $owner->plan_change_effective_at->toFormattedDateString() }}.
            Until then you keep your current {{ $owner->plan->label() }} access.
        </div>
    @endif

    @php
        $planPrice = fn (\App\Domain\Enums\Plan $plan) => match ($plan) {
            \App\Domain\Enums\Plan::Free => 'Free for 100 days',
            \App\Domain\Enums\Plan::Basic => '$5 / year',
            \App\Domain\Enums\Plan::Pro => '$20 / year',
        };
    @endphp

    <div class="row g-3">
        @foreach ($plans as $plan)
            <div class="col-md-4">
                <div class="card shadow-sm h-100 {{ $owner->plan === $plan ? 'border-success border-2' : '' }}">
                    <div class="card-body d-flex flex-column">
                        <h3 class="h5">
                            {{ $plan->label() }}
                            @if ($owner->plan === $plan)
                                <span class="badge bg-success">Current</span>
                            @endif
                        </h3>
                        <p class="text-muted mb-2">{{ $planPrice($plan) }}</p>

                        @if ($owner->plan === $plan)
                            @if ($plan === \App\Domain\Enums\Plan::Free)
                                <p class="small">
                                    <span class="badge bg-light text-dark border">
                                        Ends on {{ $owner->freeTierEndsAt()->toFormattedDateString() }}
                                    </span>
                                </p>
                            @elseif ($owner->pending_plan)
                                {{-- A scheduled downgrade/cancellation (via the Paddle
                                     customer portal) means this plan won't renew — it
                                     just runs out at the end of the paid period. --}}
                                <p class="small">
                                    <span class="badge bg-light text-dark border">
                                        Ends on {{ $owner->plan_change_effective_at->toFormattedDateString() }}
                                    </span>
                                </p>
                            @elseif ($owner->plan_renews_at)
                                <p class="mb-2">
                                    <span class="badge bg-light text-dark border">
                                        Renews on {{ $owner->plan_renews_at->toFormattedDateString() }}
                                    </span>
                                </p>
                            @endif
                        @endif

                        <ul class="small text-muted">
                            <li>{{ $plan->willReceiverLimit() }} trusted {{ Str::plural('contact', $plan->willReceiverLimit()) }}</li>
                            @if ($plan->allowsCustomMessages())
                                <li>Custom message per contact</li>
                            @endif
                        </ul>

                        <div class="mt-auto">
                            {{-- Only ever offer a button for a strictly higher tier than the
                                 owner's current one — downgrades go through the Paddle customer
                                 portal below (SPEC.md §12: deferred to period end, not an
                                 in-app button). --}}
                            @if ($plan->willReceiverLimit() > $owner->plan->willReceiverLimit())
                                @if ($owner->paddle_subscription_id)
                                    {{-- Already paying: change the price on the existing
                                         subscription (prorated, immediate) instead of opening a
                                         second, parallel checkout. --}}
                                    <form
                                        method="POST"
                                        action="{{ route('billing.upgrade', $plan->value, false) }}"
                                        class="js-confirm-upgrade"
                                        data-confirm-message="You'll be billed now for the {{ $plan->label() }} plan ({{ $planPrice($plan) }}, prorated for the rest of your billing period). Continue?"
                                    >
                                        @csrf
                                        <button type="submit" class="btn btn-dark w-100">Upgrade</button>
                                    </form>
                                @else
                                    <button
                                        type="button"
                                        class="btn btn-dark w-100 paddle-checkout-button"
                                        data-price-id="{{ $prices[$plan->value] ?? '' }}"
                                        @disabled(blank($prices[$plan->value] ?? null))
                                    >
                                        Upgrade
                                    </button>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if ($owner->plan !== \App\Domain\Enums\Plan::Free)
        <form method="POST" action="{{ route('billing.portal', [], false) }}" class="mt-4">
            @csrf
            <button type="submit" class="btn btn-danger">Manage or cancel subscription</button>
        </form>
    @endif

    <script>
        // Not inlined as an onsubmit="confirm(...)" attribute: the message
        // contains quotes, and Blade's json directive doesn't hex-escape
        // them for safe embedding in a double-quoted HTML attribute, which
        // breaks the attribute itself rather than just the confirm call. A
        // data attribute (Blade-escaped via its usual double-brace syntax)
        // plus a listener avoids that entirely.
        document.querySelectorAll('.js-confirm-upgrade').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!confirm(form.dataset.confirmMessage)) {
                    event.preventDefault();
                }
            });
        });
    </script>

    @if ($clientToken)
        <script src="https://cdn.paddle.com/paddle/v2/paddle.js"></script>
        <script>
            Paddle.Environment.set(@json($sandbox ? 'sandbox' : 'production'));

            // Paddle's overlay shows its own success screen and doesn't close
            // itself — the customer would otherwise have to close it manually
            // before seeing the updated plan. We can't just reload after a
            // fixed delay either: the webhook that actually applies the plan
            // change is a separate, asynchronous round trip to Paddle and
            // back, and isn't guaranteed to have landed yet. So instead we
            // poll our own /billing/status until it reports a plan different
            // from the one this page was rendered with, then reload for
            // real — with a generous timeout as a fallback in case the
            // webhook is unusually slow or never arrives.
            const planAtPageLoad = @json($owner->plan->value);

            function waitForPlanChangeThenReload(attempt = 0) {
                if (attempt >= 20) {
                    Paddle.Checkout.close();
                    window.location.reload();
                    return;
                }

                // Relative, not absolute: APP_URL is http:// (the app only
                // ever sees plain http behind the tunnel/proxy that
                // terminates TLS in front of it), so an absolute route()
                // URL here would be http:// even on an https:// page —
                // which browsers block as mixed content. A relative path
                // resolves against the page's own scheme instead.
                fetch(@json(route('billing.status', [], false)))
                    .then(function (response) { return response.json(); })
                    .then(function (data) {
                        if (data.plan !== planAtPageLoad) {
                            Paddle.Checkout.close();
                            window.location.reload();
                        } else {
                            setTimeout(function () { waitForPlanChangeThenReload(attempt + 1); }, 1000);
                        }
                    });
            }

            Paddle.Initialize({
                token: @json($clientToken),
                eventCallback: function (event) {
                    if (event.name === 'checkout.completed') {
                        waitForPlanChangeThenReload();
                    }
                },
            });

            document.querySelectorAll('.paddle-checkout-button').forEach((button) => {
                button.addEventListener('click', () => {
                    const priceId = button.dataset.priceId;

                    if (!priceId) {
                        return;
                    }

                    Paddle.Checkout.open({
                        items: [{ priceId, quantity: 1 }],
                        customData: { user_id: @json($owner->id) },
                        customer: { email: @json($owner->email) },
                    });
                });
            });
        </script>
    @endif
</x-app-layout>
