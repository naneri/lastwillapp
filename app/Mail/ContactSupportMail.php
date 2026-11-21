<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactSupportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $messageSubject,
        public string $body,
    ) {}

    public function build(): self
    {
        return $this->subject('[Support] '.$this->messageSubject)
            ->replyTo($this->user->email, $this->user->name)
            ->view('emails.contact-support');
    }
}
