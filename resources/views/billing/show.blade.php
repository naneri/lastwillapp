<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">{{ __('Plan & billing') }}</h2>
    </x-slot>

    @if (session('status') === 'billing-not-configured')
        <div class="alert alert-warning">Billing isn't set up on this server yet.</div>
    @elseif (session('status') === 'billing-portal-unavailable')
        <div class="alert alert-danger">Couldn't reach the billing portal right now — please try again shortly.</div>
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

    <div class="row g-3">
        @foreach ($plans as $plan)
            <div class="col-md-4">
                <div class="card shadow-sm h-100 {{ $owner->plan === $plan ? 'border-dark' : '' }}">
                    <div class="card-body d-flex flex-column">
                        <h3 class="h5">
                            {{ $plan->label() }}
                            @if ($owner->plan === $plan)
                                <span class="badge bg-dark">Current</span>
                            @endif
                        </h3>
                        <p class="text-muted mb-2">
                            @switch($plan)
                                @case(\App\Domain\Enums\Plan::Free) Free for 100 days @break
                                @case(\App\Domain\Enums\Plan::Basic) $5 / year @break
                                @case(\App\Domain\Enums\Plan::Pro) $20 / year @break
                            @endswitch
                        </p>
                        <ul class="small text-muted">
                            <li>{{ $plan->willReceiverLimit() }} trusted {{ Str::plural('contact', $plan->willReceiverLimit()) }}</li>
                            @if ($plan->allowsCustomMessages())
                                <li>Custom message per contact</li>
                            @endif
                        </ul>

                        <div class="mt-auto">
                            @if ($owner->plan !== $plan && $plan !== \App\Domain\Enums\Plan::Free)
                                <button
                                    type="button"
                                    class="btn btn-dark w-100 paddle-checkout-button"
                                    data-price-id="{{ $prices[$plan->value] ?? '' }}"
                                    @disabled(blank($prices[$plan->value] ?? null))
                                >
                                    {{ $owner->plan->willReceiverLimit() < $plan->willReceiverLimit() ? 'Upgrade' : 'Switch' }}
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if ($owner->plan !== \App\Domain\Enums\Plan::Free)
        <form method="POST" action="{{ route('billing.portal') }}" class="mt-4">
            @csrf
            <button type="submit" class="btn btn-outline-dark">Manage or cancel subscription</button>
        </form>
    @endif

    @if ($clientToken)
        <script src="https://cdn.paddle.com/paddle/v2/paddle.js"></script>
        <script>
            Paddle.Environment.set(@json($sandbox ? 'sandbox' : 'production'));
            Paddle.Initialize({ token: @json($clientToken) });

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
