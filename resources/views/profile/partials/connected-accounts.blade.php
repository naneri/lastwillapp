<section>
    <header>
        <h2 class="h5">
            {{ __('Connected accounts') }}
        </h2>

        <p class="text-muted small mt-1">
            {{ __('Social accounts you can sign in with.') }}
        </p>
    </header>

    @php
        $providers = ['google' => 'Google'];
        $connected = $user->socialAccounts->keyBy('provider');
    @endphp

    <ul class="list-group list-group-flush mt-4">
        @foreach ($providers as $key => $label)
            @php($account = $connected->get($key))

            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                <span>
                    <i class="bi bi-{{ $key }} me-2"></i>
                    {{ $label }}
                </span>

                @if ($account)
                    <span class="badge text-bg-success">
                        {{ __('Connected :date', ['date' => $account->created_at->toFormattedDateString()]) }}
                    </span>
                @else
                    <span class="badge text-bg-secondary">{{ __('Not connected') }}</span>
                @endif
            </li>
        @endforeach
    </ul>
</section>
