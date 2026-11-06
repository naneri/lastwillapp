<?php

namespace App\Http\Controllers;

use App\Services\CheckInLifecycle;
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
            'body' => ['nullable', 'string', 'max:20000'],
        ]);

        $request->user()->record()->updateOrCreate([], $validated);

        $lifecycle->activateIfReady($request->user());

        return redirect()->route('record.edit')->with('status', 'record-updated');
    }
}
