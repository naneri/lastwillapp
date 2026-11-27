<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">{{ __('Trusted contacts') }}</h2>
    </x-slot>

    @if (session('status') === 'will-receivers-updated')
        <div class="alert alert-success">{{ __('Saved.') }}</div>
    @elseif (session('status') === 'will-receivers-removed')
        <div class="alert alert-success">{{ __('Contact removed.') }}</div>
    @endif

    @if ($overLimitCount > 0)
        <div class="alert alert-warning">
            Your current plan only supports {{ $limit }}
            {{ Str::plural('trusted contact', $limit) }}, but you have
            {{ $overLimitCount }} more saved than that. The extra
            {{ Str::plural('contact', $overLimitCount) }} won't be notified
            and can't be edited here until you remove enough to get back
            within your plan's limit, or upgrade.
        </div>
    @endif

    <p class="text-muted">
        These are the people who will be emailed your record if you stop
        checking in, notified in order — the next one is only notified if
        the previous one doesn't confirm receipt within 3 days. Your plan
        allows up to {{ $limit }}.
    </p>

    <div class="list-group shadow-sm">
        @for ($rank = 1; $rank <= $limit; $rank++)
            @php($receiver = $willReceivers->get($rank))

            <div class="list-group-item d-flex justify-content-between align-items-center {{ $receiver ? '' : 'bg-light' }}" @if (! $receiver) style="border-style: dashed; border-color: #adb5bd;" @endif>
                <div>
                    <div class="fw-semibold">
                        {{ match ($rank) { 1 => '1st', 2 => '2nd', 3 => '3rd', default => $rank.'th' } }} contact
                        @if ($rank > 1) <span class="text-muted fw-normal">(optional)</span> @endif
                    </div>
                    <div class="small text-muted">
                        @if ($receiver)
                            {{ $receiver->name ?: $receiver->email }}
                            @if ($receiver->name) &middot; {{ $receiver->email }} @endif
                        @else
                            <span class="badge text-bg-secondary opacity-50">Details required</span>
                        @endif
                    </div>
                </div>
                <a href="{{ route('will-receivers.edit', $rank) }}" class="btn btn-sm btn-success">{{ $receiver ? 'Edit' : 'Add contact' }}</a>
            </div>
        @endfor

        @if ($limit < 2)
            <div class="list-group-item d-flex justify-content-between align-items-center bg-light opacity-50" style="border-style: dashed; border-color: #adb5bd;">
                <div>
                    <div class="fw-semibold">2nd contact <span class="text-muted fw-normal">(optional)</span></div>
                    <div class="small text-muted">Only available on the Basic plan or higher.</div>
                </div>
                <button type="button" class="btn btn-sm btn-success" disabled>Add contact</button>
            </div>
        @endif
    </div>

    @if ($limit < 2)
        <p class="text-muted small mt-2">
            <a href="{{ route('billing.show') }}">Upgrade to Basic</a> to add a second trusted contact.
        </p>
    @endif
</x-app-layout>
