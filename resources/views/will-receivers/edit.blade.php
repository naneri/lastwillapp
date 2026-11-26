<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">{{ __('Trusted contacts') }}</h2>
    </x-slot>

    <p class="mb-4">
        <a href="{{ route('will-receivers.index') }}" class="text-decoration-none text-muted small">&larr; Back to trusted contacts</a>
    </p>

    <div class="card shadow-sm">
        <div class="card-body">
            <h3 class="h6">
                {{ match ($rank) { 1 => '1st', 2 => '2nd', 3 => '3rd', default => $rank.'th' } }} contact
                @if ($rank > 1) <span class="text-muted">(optional)</span> @endif
            </h3>

            <form method="POST" action="{{ route('will-receivers.update', $rank) }}">
                @csrf
                @method('put')

                <div class="row mb-3">
                    <div class="col-sm-5">
                        <x-input-label for="name" value="{{ __('Name (optional)') }}" />
                        <x-text-input id="name" name="name" type="text" :value="old('name', $receiver?->name)" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <div class="col-sm-7">
                        <x-input-label for="email" value="{{ __('Email') }}" />
                        <div class="input-group">
                            <span
                                class="input-group-text"
                                data-bs-toggle="tooltip"
                                data-bs-title="{{ __("Double-check this address for typos — if it's wrong, your trusted contact won't receive your record.") }}"
                            >
                                <i class="bi bi-info-circle"></i>
                            </span>
                            <x-text-input id="email" name="email" type="email" required :value="old('email', $receiver?->email)" />
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>
                </div>

                @if ($allowsCustomMessages)
                    <div class="mb-3">
                        <x-input-label for="message" value="{{ __('Custom message (optional)') }}" />
                        <textarea id="message" name="message" rows="3" class="form-control">{{ old('message', $receiver?->message) }}</textarea>
                        <div class="form-text">Sent alongside your record if this contact ever receives it.</div>
                        <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    </div>
                @endif

                <div class="d-flex justify-content-between">
                    <x-primary-button>{{ __('Save') }}</x-primary-button>

                    @if ($receiver && $rank > 1)
                        <button
                            type="submit"
                            form="delete-contact-{{ $rank }}"
                            class="btn btn-danger"
                            onclick="return confirm('Remove this trusted contact? This cannot be undone.');"
                        >
                            {{ __('Delete contact') }}
                        </button>
                    @endif
                </div>
            </form>

            @if ($receiver && $rank > 1)
                <form id="delete-contact-{{ $rank }}" method="POST" action="{{ route('will-receivers.destroy', $rank) }}" class="d-none">
                    @csrf
                    @method('delete')
                </form>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => new bootstrap.Tooltip(el));
        });
    </script>
</x-app-layout>
