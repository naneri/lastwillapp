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
        ]);
    }

    public function update(Request $request, CheckInLifecycle $lifecycle): RedirectResponse
    {
        $validated = $request->validate([
            // 20,000 counts the text a reader would actually see (via
            // MaxVisibleTextLength), not Markdown syntax — the raw max is
            // just a generous sanity ceiling so a pathological amount of
            // markup can't bypass that limit entirely.
            'body' => ['nullable', 'string', 'max:200000', new MaxVisibleTextLength(20000)],
        ]);

        $request->user()->record()->updateOrCreate([], $validated);

        $lifecycle->activateIfReady($request->user());

        return redirect()->route('record.edit')->with('status', 'record-updated');
    }
}
