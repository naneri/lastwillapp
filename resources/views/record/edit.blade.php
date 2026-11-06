<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">{{ __('Your record') }}</h2>
    </x-slot>

    @if (session('status') === 'record-updated')
        <div class="alert alert-success">{{ __('Saved.') }}</div>
    @endif

    <div class="alert alert-warning">
        <strong>Don't store actual secrets here.</strong> Passwords, bank
        account numbers, PINs, and similar should never go in this text —
        this content is reachable via emailed links, and email accounts can
        be compromised. Instead, describe <em>where</em> to find things, in a
        way only someone who can reach you in person could use:
        <ul class="mb-0">
            <li>"The password list is in the top drawer of my desk."</li>
            <li>"See the file <code>passwords.kdbx</code> on my home PC — ask my brother, he knows where the master password is written down."</li>
            <li>"Ask my sister, she knows the safe combination."</li>
        </ul>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('record.update') }}">
                @csrf
                @method('patch')

                <div class="mb-3">
                    <x-input-label for="body" value="{{ __('Record') }}" />
                    <textarea id="body" name="body" rows="12" class="form-control">{{ old('body', $record?->body) }}</textarea>
                    <x-input-error :messages="$errors->get('body')" class="mt-2" />
                </div>

                <x-primary-button>{{ __('Save') }}</x-primary-button>
            </form>
        </div>
    </div>
</x-app-layout>
