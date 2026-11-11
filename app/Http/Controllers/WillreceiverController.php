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
        $willreceivers = $request->user()->willreceivers()->get()->keyBy('rank');

        return view('willreceivers.edit', [
            'first' => $willreceivers->get(1),
            'second' => $willreceivers->get(2),
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

        $request->user()->willreceivers()->updateOrCreate(
            ['rank' => 1],
            ['name' => $validated['first_name'], 'email' => $validated['first_email']],
        );

        $request->user()->willreceivers()->updateOrCreate(
            ['rank' => 2],
            ['name' => $validated['second_name'], 'email' => $validated['second_email']],
        );

        $lifecycle->activateIfReady($request->user());

        return redirect()->route('willreceivers.edit')->with('status', 'willreceivers-updated');
    }
}
