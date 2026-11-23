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

        $planAmount = fn (\App\Domain\Enums\Plan $plan) => match ($plan) {
            \App\Domain\Enums\Plan::Free => 'Free',
            \App\Domain\Enums\Plan::Basic => '$5',
            \App\Domain\Enums\Plan::Pro => '$20',
        };

        $planSuffix = fn (\App\Domain\Enums\Plan $plan) => match ($plan) {
            \App\Domain\Enums\Plan::Free => 'for 100 days',
            \App\Domain\Enums\Plan::Basic, \App\Domain\Enums\Plan::Pro => '/ year',
        };

        $planTagline = fn (\App\Domain\Enums\Plan $plan) => match ($plan) {
            \App\Domain\Enums\Plan::Free => 'Try it out before you subscribe.',
            \App\Domain\Enums\Plan::Basic => 'For one trusted contact.',
            \App\Domain\Enums\Plan::Pro => 'For more contacts, with custom messages.',
        };
    @endphp

    <div class="row g-4 row-cols-1 row-cols-md-3 mb-4">
        @foreach ($plans as $plan)
            <div class="col">
                <div class="card h-100 {{ $owner->plan === $plan ? 'border-success shadow-sm position-relative' : '' }}">
                    @if ($owner->plan === $plan)
                        <span class="badge text-bg-success position-absolute top-0 start-50 translate-middle">Current</span>
                    @endif

                    <div class="card-body p-4">
                        <h5 class="fw-semibold">{{ $plan->label() }}</h5>
                        <p class="text-secondary small mb-3">{{ $planTagline($plan) }}</p>

                        <div class="mb-3">
                            <span class="display-5 fw-bold">{{ $planAmount($plan) }}</span>
                            <span class="text-secondary">{{ $planSuffix($plan) }}</span>
                        </div>

                        @if ($owner->plan === $plan)
                            @if ($plan === \App\Domain\Enums\Plan::Free)
                                <p class="small mb-3">
                                    <span class="badge bg-light text-dark border">
                                        Ends on {{ $owner->freeTierEndsAt()->toFormattedDateString() }}
                                    </span>
                                </p>
                            @elseif ($owner->pending_plan)
                                {{-- A scheduled downgrade/cancellation (via the Paddle
                                     customer portal) means this plan won't renew — it
                                     just runs out at the end of the paid period. --}}
                                <p class="small mb-3">
                                    <span class="badge bg-light text-dark border">
                                        Ends on {{ $owner->plan_change_effective_at->toFormattedDateString() }}
                                    </span>
                                </p>
                            @elseif ($owner->plan_renews_at)
                                <p class="small mb-3">
                                    <span class="badge bg-light text-dark border">
                                        Renews on {{ $owner->plan_renews_at->toFormattedDateString() }}
                                    </span>
                                </p>
                            @endif
                        @endif

                        {{-- Only ever offer a button for a strictly higher tier than the
                             owner's current one — downgrades go through the Paddle customer
                             portal below (SPEC.md §12: deferred to period end, not an
                             in-app button). --}}
                        @if ($plan->willReceiverLimit() > $owner->plan->willReceiverLimit())
                            @if ($plan === \App\Domain\Enums\Plan::Pro)
                                {{-- Pro is temporarily not purchasable from the UI — swap
                                     this back for the two branches below (the same ones
                                     Basic uses) to re-enable it. See the comment on
                                     BillingController@upgrade for why that route itself
                                     is left working as-is in the meantime. --}}
                                <button type="button" class="btn btn-dark w-100 mb-3 opacity-50" disabled>
                                    Coming soon
                                </button>
                            @elseif ($owner->paddle_subscription_id)
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
                                    <button type="submit" class="btn btn-dark w-100 mb-3">Upgrade</button>
                                </form>
                            @else
                                <button
                                    type="button"
                                    class="btn btn-dark w-100 mb-3 paddle-checkout-button"
                                    data-price-id="{{ $prices[$plan->value] ?? '' }}"
                                    @disabled(blank($prices[$plan->value] ?? null))
                                >
                                    Upgrade
                                </button>
                            @endif
                        @elseif ($plan->willReceiverLimit() < $owner->plan->willReceiverLimit() && $plan !== \App\Domain\Enums\Plan::Free)
                            {{-- Downgrading between paid tiers isn't self-service (SPEC.md
                                 §12 only covers the free-tier-cancellation path via the
                                 Paddle portal below) — send them to support instead. --}}
                            <button type="button" class="btn btn-outline-dark w-100 mb-3" data-bs-toggle="modal" data-bs-target="#downgrade-modal">
                                Downgrade
                            </button>
                        @endif

                        <ul class="list-unstyled small mb-0">
                            <li class="mb-2">
                                <i class="bi bi-check-circle-fill text-success me-2" aria-hidden="true"></i>
                                {{ $plan->willReceiverLimit() }} trusted {{ Str::plural('contact', $plan->willReceiverLimit()) }}
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle-fill text-success me-2" aria-hidden="true"></i>
                                {{ number_format($plan->recordCharacterLimit()) }}-character record
                            </li>
                            <li class="{{ $plan->allowsCustomMessages() ? '' : 'text-secondary' }}">
                                <i class="bi {{ $plan->allowsCustomMessages() ? 'bi-check-circle-fill text-success' : 'bi-dash-circle' }} me-2" aria-hidden="true"></i>
                                Custom message per contact
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="modal fade" id="downgrade-modal" tabindex="-1" aria-labelledby="downgrade-modal-label" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title h5" id="downgrade-modal-label">Downgrading your plan</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>
                        To downgrade to a lower plan, please contact support at
                        <a href="mailto:{{ config('mail.from.address') }}">{{ config('mail.from.address') }}</a>.
                    </p>
                    <p class="mb-0 text-muted small">
                        A downgrade can mean losing access to some of your trusted contacts, so we handle it by hand to make sure it's what you want.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Got it</button>
                </div>
            </div>
        </div>
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
