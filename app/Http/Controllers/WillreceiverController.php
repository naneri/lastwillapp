<?php

namespace App\Http\Controllers;

use App\Domain\Services\CheckInLifecycle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WillreceiverController extends Controller
{
    public function edit(Request $request): View
    {
        $willReceivers = $request->user()->willReceivers()->get()->keyBy('rank');

        return view('willreceivers.edit', [
            'first' => $willReceivers->get(1),
            'second' => $willReceivers->get(2),
        ]);
    }

    public function update(Request $request, CheckInLifecycle $lifecycle): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['nullable', 'string', 'max:255'],
            'first_email' => ['required', 'email', 'max:255'],
            'second_name' => ['nullable', 'string', 'max:255'],
            'second_email' => ['required', 'email', 'max:255', 'different:first_email'],
        ]);

        $request->user()->willReceivers()->updateOrCreate(
            ['rank' => 1],
            ['name' => $validated['first_name'], 'email' => $validated['first_email']],
        );

        $request->user()->willReceivers()->updateOrCreate(
            ['rank' => 2],
            ['name' => $validated['second_name'], 'email' => $validated['second_email']],
        );

        $lifecycle->activateIfReady($request->user());

        return redirect()->route('willreceivers.edit')->with('status', 'willreceivers-updated');
    }
}
