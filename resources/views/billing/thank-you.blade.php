<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">{{ __('Thank you') }}</h2>
    </x-slot>

    <div class="card shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-check-circle-fill text-success display-4 mb-3 d-block"></i>
            <h3 class="h5">You're all set{{ $plan ? ' on '.$plan->label() : '' }}.</h3>
            <p class="text-muted">Your subscription is active.</p>
            <a href="{{ route('dashboard') }}" class="btn btn-dark mt-2">Go to dashboard</a>
        </div>
    </div>

    @if ($plan)
        {{-- Google tag (gtag.js) event --}}
        <script>
            if (typeof gtag === 'function') {
                gtag('event', 'conversion_event_purchase', {
                    value: @json($plan->price()),
                    currency: 'USD',
                    transaction_id: @json($transactionId ?? ''),
                });
            }
        </script>
    @endif
</x-app-layout>
