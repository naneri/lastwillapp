<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">{{ __('Help & Support') }}</h2>
    </x-slot>

    @if (session('status') === 'message-sent')
        <div class="alert alert-success">{{ __("Sent — we'll get back to you by email.") }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('contact.store') }}">
                @csrf

                <div class="mb-3">
                    <x-input-label for="subject" value="{{ __('Subject') }}" />
                    <x-text-input id="subject" name="subject" type="text" class="mt-1 w-100" :value="old('subject')" required autofocus />
                    <x-input-error :messages="$errors->get('subject')" class="mt-2" />
                </div>

                <div class="mb-3">
                    <x-input-label for="body" value="{{ __('Message') }}" />
                    <textarea id="body" name="body" rows="8" class="form-control mt-1" required>{{ old('body') }}</textarea>
                    <x-input-error :messages="$errors->get('body')" class="mt-2" />
                </div>

                <x-primary-button>{{ __('Send') }}</x-primary-button>
            </form>
        </div>
    </div>
</x-app-layout>
