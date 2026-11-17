<x-guest-layout>
    <p class="login-box-msg">{{ __('Sign in to start your session') }}</p>

    <x-auth-session-status class="mb-3" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <label class="visually-hidden" for="email">{{ __('Email') }}</label>
        <div class="input-group mb-3">
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="Email" />
            <div class="input-group-text">
                <span class="bi bi-envelope"></span>
            </div>
        </div>
        <x-input-error :messages="$errors->get('email')" class="mb-3" />

        <label class="visually-hidden" for="password">{{ __('Password') }}</label>
        <div class="input-group mb-3">
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" placeholder="Password" />
            <div class="input-group-text">
                <span class="bi bi-lock-fill"></span>
            </div>
        </div>
        <x-input-error :messages="$errors->get('password')" class="mb-3" />

        <div class="row">
            <div class="col-7">
                <div class="form-check">
                    <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
                    <label for="remember_me" class="form-check-label">{{ __('Remember me') }}</label>
                </div>
            </div>
            <div class="col-5">
                <div class="d-grid">
                    <x-primary-button>
                        {{ __('Log in') }}
                    </x-primary-button>
                </div>
            </div>
        </div>
    </form>

    @if (Route::has('password.request'))
        <p class="mb-1">
            <a href="{{ route('password.request') }}">{{ __('Forgot your password?') }}</a>
        </p>
    @endif

    @if (Route::has('register'))
        <p class="mb-0">
            <a href="{{ route('register') }}">{{ __('Need an account?') }}</a>
        </p>
    @endif
</x-guest-layout>
