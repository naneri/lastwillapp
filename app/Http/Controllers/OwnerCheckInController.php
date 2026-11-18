<?php

namespace App\Http\Controllers;

use App\Domain\Enums\OwnerActionType;
use App\Domain\Models\OwnerActionLog;
use App\Domain\Services\CheckInLifecycle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OwnerCheckInController extends Controller
{
    public function store(Request $request, CheckInLifecycle $lifecycle): RedirectResponse
    {
        $lifecycle->recover($request->user());

        OwnerActionLog::create([
            'user_id' => $request->user()->id,
            'action' => OwnerActionType::CheckedIn,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('dashboard')->with('status', 'checked-in');
    }

    public function pause(Request $request, CheckInLifecycle $lifecycle): RedirectResponse
    {
        $lifecycle->pause($request->user());

        OwnerActionLog::create([
            'user_id' => $request->user()->id,
            'action' => OwnerActionType::Paused,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('dashboard')->with('status', 'checkins-paused');
    }
}
