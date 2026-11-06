<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">{{ __('Trusted contacts') }}</h2>
    </x-slot>

    @if (session('status') === 'willreceivers-updated')
        <div class="alert alert-success">{{ __('Saved.') }}</div>
    @endif

    <p class="text-muted">
        These are the two people who will be emailed your record if you stop
        checking in. The first contact is notified first; the second is only
        notified if the first doesn't confirm receipt within 3 days.
    </p>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('willreceivers.update') }}">
                @csrf
                @method('put')

                <h3 class="h6">First contact</h3>
                <div class="row mb-3">
                    <div class="col-sm-5">
                        <x-input-label for="first_name" value="{{ __('Name (optional)') }}" />
                        <x-text-input id="first_name" name="first_name" type="text" :value="old('first_name', $first?->name)" />
                        <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                    </div>
                    <div class="col-sm-7">
                        <x-input-label for="first_email" value="{{ __('Email') }}" />
                        <x-text-input id="first_email" name="first_email" type="email" required :value="old('first_email', $first?->email)" />
                        <x-input-error :messages="$errors->get('first_email')" class="mt-2" />
                    </div>
                </div>

                <h3 class="h6 mt-4">Second contact</h3>
                <div class="row mb-3">
                    <div class="col-sm-5">
                        <x-input-label for="second_name" value="{{ __('Name (optional)') }}" />
                        <x-text-input id="second_name" name="second_name" type="text" :value="old('second_name', $second?->name)" />
                        <x-input-error :messages="$errors->get('second_name')" class="mt-2" />
                    </div>
                    <div class="col-sm-7">
                        <x-input-label for="second_email" value="{{ __('Email') }}" />
                        <x-text-input id="second_email" name="second_email" type="email" required :value="old('second_email', $second?->email)" />
                        <x-input-error :messages="$errors->get('second_email')" class="mt-2" />
                    </div>
                </div>

                <x-primary-button>{{ __('Save') }}</x-primary-button>
            </form>
        </div>
    </div>
</x-app-layout>
