<?php

namespace Tests\Feature\Auth;

use App\Domain\Enums\OwnerStatus;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class SocialAuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_new_google_user_gets_an_account_and_is_logged_in(): void
    {
        Socialite::fake('google', SocialiteUser::fake([
            'id' => 'google-123',
            'email' => 'new-via-google@example.com',
            'name' => 'Nia Newcomer',
        ]));

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $user = User::where('email', 'new-via-google@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('Nia Newcomer', $user->name);
        $this->assertNull($user->password);
        $this->assertNotNull($user->email_verified_at);
        $this->assertSame(OwnerStatus::Setup, $user->status);

        $this->assertSame(1, SocialAccount::where('user_id', $user->id)->count());
        $this->assertSame('google', $user->socialAccounts->first()->provider);
        $this->assertSame('google-123', $user->socialAccounts->first()->provider_id);
    }

    public function test_a_returning_google_user_is_logged_in_without_creating_a_duplicate(): void
    {
        $user = User::factory()->create();
        SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google-456',
        ]);

        Socialite::fake('google', SocialiteUser::fake([
            'id' => 'google-456',
            'email' => 'someone-else@example.com',
        ]));

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertSame(1, User::count());
    }

    public function test_google_login_links_to_an_existing_account_with_the_same_email_instead_of_duplicating_it(): void
    {
        $user = User::factory()->create(['email' => 'same-email@example.com']);

        Socialite::fake('google', SocialiteUser::fake([
            'id' => 'google-789',
            'email' => 'same-email@example.com',
        ]));

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertSame(1, User::count());

        $this->assertSame(1, SocialAccount::where('user_id', $user->id)->count());
        $this->assertSame('google-789', $user->socialAccounts->first()->provider_id);
    }

    public function test_password_login_is_rejected_with_a_clear_message_for_a_social_only_account(): void
    {
        $user = User::factory()->create(['password' => null]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'whatever-they-try',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString(
            'Google',
            session('errors')->get('email')[0],
        );
        $this->assertGuest();
    }
}
