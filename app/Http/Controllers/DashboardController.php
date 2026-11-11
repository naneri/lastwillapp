<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('dashboard', [
            'owner' => $user,
            'hasRecord' => filled($user->record?->body),
            'willreceiverCount' => $user->willReceivers()->count(),
        ]);
    }
}
