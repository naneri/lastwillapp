<?php

namespace App\Http\Controllers;

use App\Domain\Rules\MaxVisibleTextLength;
use App\Domain\Services\CheckInLifecycle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecordController extends Controller
{
    public function edit(Request $request): View
    {
        return view('record.edit', [
            'record' => $request->user()->record,
            'plan' => $request->user()->plan,
            'maxVisibleLength' => $request->user()->plan->recordCharacterLimit(),
        ]);
    }

    public function update(Request $request, CheckInLifecycle $lifecycle): RedirectResponse
    {
        $maxVisibleLength = $request->user()->plan->recordCharacterLimit();

        $validated = $request->validate([
            // The raw max is just a generous sanity ceiling so a
            // pathological amount of markup can't bypass the visible-text
            // limit entirely.
            'body' => ['nullable', 'string', 'max:'.($maxVisibleLength * 10), new MaxVisibleTextLength($maxVisibleLength)],
        ]);

        $request->user()->record()->updateOrCreate([], $validated);

        $lifecycle->activateIfReady($request->user());

        return redirect()->route('record.edit')->with('status', 'record-updated');
    }
}
