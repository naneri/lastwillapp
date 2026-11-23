<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Pricing — {{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container py-5" style="max-width: 60rem;">
            <p class="mb-4">
                <a href="/" class="text-decoration-none text-muted small">&larr; Back to home</a>
            </p>

            <h1 class="h3 mb-2">Pricing</h1>
            <p class="text-muted mb-4">
                Every account starts with a 100-day free trial — monitoring
                and one trusted contact included, no card required.
            </p>

            @php
                // Mirrors billing/show.blade.php's pricing cards — kept as
                // a separate, simpler copy rather than a shared partial
                // since this page has no logged-in owner, no "current
                // plan" state, and no Paddle checkout wiring.
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
                    \App\Domain\Enums\Plan::Basic => 'For two trusted contacts.',
                    \App\Domain\Enums\Plan::Pro => 'More contacts, custom messages.',
                };
            @endphp

            <div class="row g-4 row-cols-1 row-cols-md-3 mb-4">
                @foreach ($plans as $plan)
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-body p-4">
                                <h5 class="fw-semibold">{{ $plan->label() }}</h5>
                                <p class="text-secondary small mb-3">{{ $planTagline($plan) }}</p>

                                <div class="mb-3">
                                    <span class="display-5 fw-bold">{{ $planAmount($plan) }}</span>
                                    <span class="text-secondary">{{ $planSuffix($plan) }}</span>
                                </div>

                                @if ($plan === \App\Domain\Enums\Plan::Pro)
                                    {{-- Temporarily not purchasable — see the same note on
                                         billing/show.blade.php's Pro card. --}}
                                    <button type="button" class="btn btn-dark w-100 mb-3 opacity-50" disabled>
                                        Coming soon
                                    </button>
                                @else
                                    <a href="{{ route('register') }}" class="btn btn-dark w-100 mb-3">
                                        Get started
                                    </a>
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

            <p class="mb-0 text-center">
                Questions about what's included? Read our <a href="{{ route('faq') }}">FAQ</a>.
            </p>
        </div>
    </body>
</html>
