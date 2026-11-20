<?php

namespace App\Http\Controllers;

use App\Domain\Rules\MaxVisibleTextLength;
use App\Domain\Services\CheckInLifecycle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecordController extends Controller
{
    /**
     * Counts the text a reader would actually see (via
     * MaxVisibleTextLength), not Markdown syntax — shared with the
     * editor's live character counter so the two never drift apart.
     */
    const int MAX_VISIBLE_LENGTH = 20000;

    public function edit(Request $request): View
    {
        return view('record.edit', [
            'record' => $request->user()->record,
            'maxVisibleLength' => self::MAX_VISIBLE_LENGTH,
        ]);
    }

    public function update(Request $request, CheckInLifecycle $lifecycle): RedirectResponse
    {
        $validated = $request->validate([
            // The raw max is just a generous sanity ceiling so a
            // pathological amount of markup can't bypass the visible-text
            // limit entirely.
            'body' => ['nullable', 'string', 'max:200000', new MaxVisibleTextLength(self::MAX_VISIBLE_LENGTH)],
        ]);

        $request->user()->record()->updateOrCreate([], $validated);

        $lifecycle->activateIfReady($request->user());

        return redirect()->route('record.edit')->with('status', 'record-updated');
    }
}
