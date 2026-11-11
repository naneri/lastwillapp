<?php

namespace App\Http\Controllers;

use App\Domain\Services\CheckInLifecycle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class WillReceiverController extends Controller
{
    public function edit(Request $request): View
    {
        $owner = $request->user();
        $limit = $owner->plan->willReceiverLimit();

        return view('will-receivers.edit', [
            'limit' => $limit,
            'willReceivers' => $owner->willReceivers()->get()->keyBy('rank'),
            'allowsCustomMessages' => $owner->plan->allowsCustomMessages(),
            'overLimitCount' => max(0, $owner->willReceivers()->count() - $limit),
        ]);
    }

    public function update(Request $request, CheckInLifecycle $lifecycle): RedirectResponse
    {
        $owner = $request->user();
        $limit = $owner->plan->willReceiverLimit();
        $allowsMessages = $owner->plan->allowsCustomMessages();

        // Only ranks up to the owner's current plan limit can be
        // submitted (SPEC.md §12) — the edit form never renders fields
        // beyond this, so anything further is ignored, not validated.
        $rules = [];
        for ($rank = 1; $rank <= $limit; $rank++) {
            $rules["receivers.$rank.name"] = ['nullable', 'string', 'max:255'];
            $rules["receivers.$rank.email"] = [$rank === 1 ? 'required' : 'nullable', 'email', 'max:255'];
            $rules["receivers.$rank.message"] = ['nullable', 'string', 'max:5000'];
        }

        $validated = $request->validate($rules);

        $emails = collect($validated['receivers'] ?? [])->pluck('email')->filter();

        if ($emails->count() !== $emails->unique()->count()) {
            throw ValidationException::withMessages([
                'receivers' => 'Each trusted contact needs a different email address.',
            ]);
        }

        for ($rank = 1; $rank <= $limit; $rank++) {
            $data = $validated['receivers'][$rank] ?? null;

            if (blank($data['email'] ?? null)) {
                $owner->willReceivers()->where('rank', $rank)->delete();

                continue;
            }

            $owner->willReceivers()->updateOrCreate(
                ['rank' => $rank],
                [
                    'name' => $data['name'] ?? null,
                    'email' => $data['email'],
                    'message' => $allowsMessages ? ($data['message'] ?? null) : null,
                ],
            );
        }

        $lifecycle->activateIfReady($owner);

        return redirect()->route('will-receivers.edit')->with('status', 'will-receivers-updated');
    }
}
