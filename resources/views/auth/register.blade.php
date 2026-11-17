<x-guest-layout>
    <p class="login-box-msg">{{ __('Register a new account') }}</p>

    <form method="POST" action="{{ route('register') }}" id="register-form">
        @csrf

        <input type="hidden" name="timezone" id="timezone" value="{{ old('timezone') }}">

        <label class="visually-hidden" for="name">{{ __('Name') }}</label>
        <div class="input-group mb-3">
            <x-text-input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Full Name" />
            <div class="input-group-text">
                <span class="bi bi-person"></span>
            </div>
        </div>
        <x-input-error :messages="$errors->get('name')" class="mb-3" />

        <label class="visually-hidden" for="email">{{ __('Email') }}</label>
        <div class="input-group mb-3">
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="Email" />
            <div class="input-group-text">
                <span class="bi bi-envelope"></span>
            </div>
        </div>
        <x-input-error :messages="$errors->get('email')" class="mb-3" />

        <label class="visually-hidden" for="password">{{ __('Password') }}</label>
        <div class="input-group mb-3">
            <x-text-input id="password" type="password" name="password" required autocomplete="new-password" placeholder="Password" />
            <div class="input-group-text">
                <span class="bi bi-lock-fill"></span>
            </div>
        </div>
        <x-input-error :messages="$errors->get('password')" class="mb-3" />

        <label class="visually-hidden" for="password_confirmation">{{ __('Confirm Password') }}</label>
        <div class="input-group mb-3">
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Confirm Password" />
            <div class="input-group-text">
                <span class="bi bi-lock-fill"></span>
            </div>
        </div>
        <x-input-error :messages="$errors->get('password_confirmation')" class="mb-3" />

        <div class="row">
            <div class="col-8">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="terms" name="terms" value="1" @checked(old('terms'))>
                    <label class="form-check-label" for="terms">
                        {{ __('I agree to the') }} <a href="{{ route('terms') }}">{{ __('terms') }}</a>
                    </label>
                </div>
                <x-input-error :messages="$errors->get('terms')" />
            </div>
            <div class="col-4">
                <div class="d-grid">
                    <x-primary-button>
                        {{ __('Register') }}
                    </x-primary-button>
                </div>
            </div>
        </div>
    </form>

    <p class="mb-0 mt-3">
        <a href="{{ route('login') }}">{{ __('Already registered?') }}</a>
    </p>

    <script>
        // Best-effort: detect the browser's IANA timezone so new accounts
        // don't start on the UTC default until someone finds the profile
        // page and changes it by hand. Left blank if detection throws
        // (e.g. a very old browser) — the server falls back to UTC.
        try {
            document.getElementById('timezone').value = Intl.DateTimeFormat().resolvedOptions().timeZone;
        } catch (e) {}
    </script>
</x-guest-layout>
