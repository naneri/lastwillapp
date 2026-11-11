<?php

namespace App\Http\Controllers;

use App\Domain\Models\CheckIn;
use App\Domain\Services\CheckInLifecycle;
use Illuminate\View\View;

class CheckInConfirmationController extends Controller
{
    public function show(string $token, CheckInLifecycle $lifecycle): View
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

        return view('checkin.confirmed', ['owner' => $checkIn->user]);
    }
}
