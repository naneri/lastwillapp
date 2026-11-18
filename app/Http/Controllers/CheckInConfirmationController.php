<?php

namespace App\Http\Controllers;

use App\Domain\Enums\OwnerActionType;
use App\Domain\Models\CheckIn;
use App\Domain\Models\OwnerActionLog;
use App\Domain\Services\CheckInLifecycle;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckInConfirmationController extends Controller
{
    public function show(string $token, Request $request, CheckInLifecycle $lifecycle): View
    {
        $checkIn = CheckIn::where('token', $token)->first();

        if (! $checkIn) {
            return view('checkin.invalid');
        }

        if ($checkIn->isConfirmed()) {
            return view('checkin.already-confirmed');
        }

        if ($checkIn->isExpired()) {
            return view('checkin.expired');
        }

        $lifecycle->confirmToken($checkIn);

        // No authenticated session on this route (it's reached straight from
        // an emailed link) — the owner is identified via the token's own
        // check-in record instead of $request->user().
        OwnerActionLog::create([
            'user_id' => $checkIn->user_id,
            'action' => OwnerActionType::CheckInConfirmed,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return view('checkin.confirmed', ['owner' => $checkIn->user]);
    }
}
