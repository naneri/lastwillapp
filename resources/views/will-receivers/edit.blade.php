<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">{{ __('Trusted contacts') }}</h2>
    </x-slot>

    @if (session('status') === 'will-receivers-updated')
        <div class="alert alert-success">{{ __('Saved.') }}</div>
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

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('will-receivers.update') }}">
                @csrf
                @method('put')

                @for ($rank = 1; $rank <= $limit; $rank++)
                    @php($receiver = $willReceivers->get($rank))

                    <h3 class="h6 {{ $rank > 1 ? 'mt-4' : '' }}">
                        {{ match ($rank) { 1 => '1st', 2 => '2nd', 3 => '3rd', default => $rank.'th' } }} contact
                        @if ($rank > 1) <span class="text-muted">(optional)</span> @endif
                    </h3>
                    <div class="row mb-3">
                        <div class="col-sm-5">
                            <x-input-label for="receivers_{{ $rank }}_name" value="{{ __('Name (optional)') }}" />
                            <x-text-input id="receivers_{{ $rank }}_name" name="receivers[{{ $rank }}][name]" type="text" :value="old('receivers.'.$rank.'.name', $receiver?->name)" />
                            <x-input-error :messages="$errors->get('receivers.'.$rank.'.name')" class="mt-2" />
                        </div>
                        <div class="col-sm-7">
                            <x-input-label for="receivers_{{ $rank }}_email" value="{{ __('Email') }}" />
                            <x-text-input id="receivers_{{ $rank }}_email" name="receivers[{{ $rank }}][email]" type="email" :required="$rank === 1" :value="old('receivers.'.$rank.'.email', $receiver?->email)" />
                            <x-input-error :messages="$errors->get('receivers.'.$rank.'.email')" class="mt-2" />
                        </div>
                    </div>

                    @if ($allowsCustomMessages)
                        <div class="mb-3">
                            <x-input-label for="receivers_{{ $rank }}_message" value="{{ __('Custom message (optional)') }}" />
                            <textarea id="receivers_{{ $rank }}_message" name="receivers[{{ $rank }}][message]" rows="3" class="form-control">{{ old('receivers.'.$rank.'.message', $receiver?->message) }}</textarea>
                            <div class="form-text">Sent alongside your record if this contact ever receives it.</div>
                            <x-input-error :messages="$errors->get('receivers.'.$rank.'.message')" class="mt-2" />
                        </div>
                    @endif
                @endfor

                <x-primary-button>{{ __('Save') }}</x-primary-button>
            </form>
        </div>
    </div>
</x-app-layout>
