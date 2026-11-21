<?php

namespace App\Http\Controllers;

use App\Mail\ContactSupportMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function create(): View
    {
        return view('contact.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:150'],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        Mail::to(config('mail.support_address'))
            ->send(new ContactSupportMail($request->user(), $validated['subject'], $validated['body']));

        return redirect()->route('contact.create')->with('status', 'message-sent');
    }
}
