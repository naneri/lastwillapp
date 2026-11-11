<?php

namespace App\Http\Controllers;

use App\Domain\Services\CheckInLifecycle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OwnerCheckInController extends Controller
{
    public function store(Request $request, CheckInLifecycle $lifecycle): RedirectResponse
    {
        $lifecycle->recover($request->user());

        return redirect()->route('dashboard')->with('status', 'checked-in');
    }
}
