<?php

namespace App\Domain\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FreeTierExpiryWarningMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $owner,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your free trial ends in 7 days — '.config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.free-tier-warning',
            with: [
                'endsAt' => $this->owner->freeTierEndsAt(),
            ],
        );
    }
}
