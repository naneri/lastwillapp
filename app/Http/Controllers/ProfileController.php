<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        if ($user->paddle_subscription_id && ! $this->cancelPaddleSubscription($user)) {
            return Redirect::route('profile.edit')
                ->with('status', 'account-deletion-failed');
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Cancels the owner's Paddle subscription before their account (and its
     * paddle_subscription_id) is deleted — otherwise Paddle would keep
     * billing them with no local record left to notice or reconcile it.
     */
    protected function cancelPaddleSubscription(User $user): bool
    {
        $apiKey = config('paddle.api_key');

        if (! $apiKey) {
            return false;
        }

        $response = Http::withToken($apiKey)
            ->post($this->apiBase()."/subscriptions/{$user->paddle_subscription_id}/cancel", [
                'effective_from' => 'immediately',
            ]);

        return $response->successful();
    }

    protected function apiBase(): string
    {
        return config('paddle.sandbox') ? 'https://sandbox-api.paddle.com' : 'https://api.paddle.com';
    }
}
