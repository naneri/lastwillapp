<?php

namespace App\Mail;

use App\Models\Reveal;
use App\Models\User;
use App\Models\Willreceiver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RevealMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $owner,
        public Willreceiver $willreceiver,
        public Reveal $reveal,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->owner->name.' set up '.config('app.name').' to reach you',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reveal',
            with: [
                'viewUrl' => route('reveal.show', $this->reveal->token),
            ],
        );
    }
}
