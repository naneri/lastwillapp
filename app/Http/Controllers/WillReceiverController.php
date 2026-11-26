<?php

namespace App\Http\Controllers;

use App\Domain\Services\CheckInLifecycle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class WillReceiverController extends Controller
{
    public function index(Request $request): View
    {
        $owner = $request->user();
        $limit = $owner->plan->willReceiverLimit();

        return view('will-receivers.index', [
            'limit' => $limit,
            'willReceivers' => $owner->willReceivers()->get()->keyBy('rank'),
            'overLimitCount' => max(0, $owner->willReceivers()->count() - $limit),
        ]);
    }

    public function edit(Request $request, int $rank): View
    {
        $owner = $request->user();

        abort_unless($rank >= 1 && $rank <= $owner->plan->willReceiverLimit(), 404);

        return view('will-receivers.edit', [
            'rank' => $rank,
            'receiver' => $owner->willReceivers()->where('rank', $rank)->first(),
            'allowsCustomMessages' => $owner->plan->allowsCustomMessages(),
        ]);
    }

    public function update(Request $request, int $rank, CheckInLifecycle $lifecycle): RedirectResponse
    {
        $owner = $request->user();

        abort_unless($rank >= 1 && $rank <= $owner->plan->willReceiverLimit(), 404);

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail) use ($owner): void {
                    if (strcasecmp($value, $owner->email) === 0) {
                        $fail("You can't add your own email address as a trusted contact.");
                    }
                },
            ],
            'message' => ['nullable', 'string', 'max:5000'],
        ]);

        $emailTaken = $owner->willReceivers()
            ->where('rank', '!=', $rank)
            ->where('email', $validated['email'])
            ->exists();

        if ($emailTaken) {
            throw ValidationException::withMessages([
                'email' => 'Each trusted contact needs a different email address.',
            ]);
        }

        $owner->willReceivers()->updateOrCreate(
            ['rank' => $rank],
            [
                'name' => $validated['name'] ?? null,
                'email' => $validated['email'],
                'message' => $owner->plan->allowsCustomMessages() ? ($validated['message'] ?? null) : null,
            ],
        );

        $lifecycle->activateIfReady($owner);

        return redirect()->route('will-receivers.index')->with('status', 'will-receivers-updated');
    }

    public function destroy(Request $request, int $rank): RedirectResponse
    {
        $owner = $request->user();

        abort_unless($rank > 1 && $rank <= $owner->plan->willReceiverLimit(), 404);

        $owner->willReceivers()->where('rank', $rank)->delete();

        return redirect()->route('will-receivers.index')->with('status', 'will-receivers-removed');
    }
}
