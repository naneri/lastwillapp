<?php

namespace App\Http\Controllers\Auth;

use App\Domain\Services\CheckInLifecycle;
use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Providers wired up so far — add to this list (and a matching
     * config/services.php entry) as more get supported. The
     * social_accounts table and the rest of this flow already support any
     * number of providers per user.
     */
    private const SUPPORTED_PROVIDERS = ['google'];

    public function redirect(string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, self::SUPPORTED_PROVIDERS, true), 404);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider, CheckInLifecycle $lifecycle): RedirectResponse
    {
        abort_unless(in_array($provider, self::SUPPORTED_PROVIDERS, true), 404);

        $socialUser = Socialite::driver($provider)->user();

        $socialAccount = SocialAccount::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($socialAccount) {
            Auth::login($socialAccount->user);

            return redirect()->route('dashboard');
        }

        // Same email as an existing account: link this provider to it
        // rather than creating a duplicate — the provider only ever hands
        // us an email it has itself verified. A different email than any
        // existing account is a separate case we deliberately don't try to
        // resolve (see FAQ-adjacent discussion): there's no reliable way
        // to know it's the same person, so it becomes a new account.
        $user = User::where('email', $socialUser->getEmail())->first();

        if (! $user) {
            $user = User::create([
                'name' => $socialUser->getName() ?: $socialUser->getNickname() ?: $socialUser->getEmail(),
                'email' => $socialUser->getEmail(),
                'password' => null,
            ]);

            // Not mass-assignable (email_verified_at isn't in User's
            // #[Fillable] list, deliberately, to keep it out of reach of
            // ordinary form input) — trusted here because the provider
            // only ever hands us an email it has itself verified.
            $user->forceFill(['email_verified_at' => now()])->save();

            $lifecycle->initialize($user);

            event(new Registered($user));
        }

        $user->socialAccounts()->create([
            'provider' => $provider,
            'provider_id' => $socialUser->getId(),
        ]);

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
