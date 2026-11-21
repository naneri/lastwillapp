<?php

namespace Tests\Feature;

use App\Mail\ContactSupportMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_send_a_message_to_support(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/contact', [
            'subject' => 'Need help',
            'body' => 'Something is broken.',
        ]);

        $response->assertRedirect(route('contact.create'));
        $response->assertSessionHas('status', 'message-sent');

        Mail::assertSent(ContactSupportMail::class, function (ContactSupportMail $mail) use ($user) {
            return $mail->user->is($user)
                && $mail->messageSubject === 'Need help'
                && $mail->body === 'Something is broken.'
                && $mail->hasTo(config('mail.support_address'));
        });
    }

    public function test_subject_and_body_are_required(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/contact', [
            'subject' => '',
            'body' => '',
        ]);

        $response->assertSessionHasErrors(['subject', 'body']);
        Mail::assertNothingSent();
    }

    public function test_guests_cannot_send_a_message(): void
    {
        $response = $this->post('/contact', [
            'subject' => 'Need help',
            'body' => 'Something is broken.',
        ]);

        $response->assertRedirect('/login');
    }
}
