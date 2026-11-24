<?php

namespace App\Domain\Mail;

use App\Domain\Enums\CheckInStage;
use App\Domain\Models\CheckIn;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CheckInMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $owner,
        public CheckIn $checkIn,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: match ($this->checkIn->stage) {
                CheckInStage::Monthly => 'Please confirm you\'re OK — '.config('app.name'),
                CheckInStage::AtRisk => 'We missed your check-in — please confirm within 4 days',
                CheckInStage::DailyRetry => 'This needs your attention today — confirm you\'re OK',
            },
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.check-in',
            with: [
                'confirmUrl' => route('checkin.confirm', $this->checkIn->token),
                'stage' => $this->checkIn->stage,
                'expiresAt' => $this->checkIn->expires_at,
            ],
        );
    }
}
