<?php

namespace App\Http\Controllers;

use App\Domain\Models\Reveal;
use App\Domain\Services\MarkdownRenderer;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RevealController extends Controller
{
    public function show(string $token, MarkdownRenderer $markdown): View
    {
        $reveal = Reveal::where('token', $token)->firstOrFail();
        $record = $reveal->user->record;

        return view('reveal.show', [
            'reveal' => $reveal,
            'owner' => $reveal->user,
            'record' => $record,
            'recordHtml' => $record ? $markdown->toHtml($record->body) : null,
        ]);
    }

    public function confirm(string $token): RedirectResponse
    {
        $reveal = Reveal::where('token', $token)->firstOrFail();

        if (! $reveal->isConfirmed()) {
            $reveal->confirmed_at = CarbonImmutable::now();
            $reveal->save();
        }

        return redirect()->route('reveal.show', $token);
    }
}
