<x-guest-layout>
    <p class="login-box-msg">{{ __('Register a new account') }}</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf

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
            <div class="col-7 d-flex align-items-center">
                <a href="{{ route('login') }}">{{ __('Already registered?') }}</a>
            </div>
            <div class="col-5">
                <div class="d-grid">
                    <x-primary-button>
                        {{ __('Register') }}
                    </x-primary-button>
                </div>
            </div>
        </div>
    </form>
</x-guest-layout>
