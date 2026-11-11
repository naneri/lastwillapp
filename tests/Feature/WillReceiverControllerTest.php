<?php

namespace Tests\Feature;

use App\Domain\Enums\Plan;
use App\Domain\Models\WillReceiver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WillReceiverControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_free_owner_can_only_save_one_willreceiver_even_if_more_are_posted(): void
    {
        $owner = User::factory()->create(['plan' => Plan::Free]);

        $response = $this->actingAs($owner)->put('/will-receivers', [
            'receivers' => [
                1 => ['email' => 'first@example.com'],
                2 => ['email' => 'second@example.com'],
            ],
        ]);

        $response->assertRedirect(route('will-receivers.edit'));
        $this->assertSame(1, $owner->willReceivers()->count());
        $this->assertSame('first@example.com', $owner->willReceivers()->first()->email);
    }

    public function test_basic_owner_can_save_two_willreceivers(): void
    {
        $owner = User::factory()->create(['plan' => Plan::Basic]);

        $this->actingAs($owner)->put('/will-receivers', [
            'receivers' => [
                1 => ['email' => 'first@example.com'],
                2 => ['email' => 'second@example.com'],
            ],
        ])->assertRedirect(route('will-receivers.edit'));

        $this->assertSame(2, $owner->willReceivers()->count());
    }

    public function test_pro_owner_can_save_three_willreceivers_with_custom_messages(): void
    {
        $owner = User::factory()->create(['plan' => Plan::Pro]);

        $this->actingAs($owner)->put('/will-receivers', [
            'receivers' => [
                1 => ['email' => 'first@example.com', 'message' => 'For you, Alice.'],
                2 => ['email' => 'second@example.com', 'message' => 'For you, Bob.'],
                3 => ['email' => 'third@example.com'],
            ],
        ])->assertRedirect(route('will-receivers.edit'));

        $this->assertSame(3, $owner->willReceivers()->count());
        $this->assertSame('For you, Alice.', $owner->willReceivers()->where('rank', 1)->first()->message);
        $this->assertSame('For you, Bob.', $owner->willReceivers()->where('rank', 2)->first()->message);
        $this->assertNull($owner->willReceivers()->where('rank', 3)->first()->message);
    }

    public function test_basic_owner_cannot_set_a_custom_message_even_if_posted(): void
    {
        $owner = User::factory()->create(['plan' => Plan::Basic]);

        $this->actingAs($owner)->put('/will-receivers', [
            'receivers' => [
                1 => ['email' => 'first@example.com', 'message' => 'Sneaky message'],
            ],
        ])->assertRedirect(route('will-receivers.edit'));

        $this->assertNull($owner->willReceivers()->where('rank', 1)->first()->message);
    }

    public function test_duplicate_emails_across_willreceivers_are_rejected(): void
    {
        $owner = User::factory()->create(['plan' => Plan::Basic]);

        $response = $this->actingAs($owner)->from('/will-receivers')->put('/will-receivers', [
            'receivers' => [
                1 => ['email' => 'same@example.com'],
                2 => ['email' => 'same@example.com'],
            ],
        ]);

        $response->assertRedirect('/will-receivers');
        $response->assertSessionHasErrors();
        $this->assertSame(0, $owner->willReceivers()->count());
    }

    public function test_downgraded_owners_excess_willreceiver_is_preserved_but_excluded(): void
    {
        $owner = User::factory()->create(['plan' => Plan::Basic]);
        $owner->willReceivers()->create(['rank' => 1, 'email' => 'first@example.com']);
        $owner->willReceivers()->create(['rank' => 2, 'email' => 'second@example.com']);
        $owner->willReceivers()->create(['rank' => 3, 'email' => 'third@example.com']);

        $this->assertSame(3, $owner->willReceivers()->count(), 'sanity: 3 rows exist even though Basic only allows 2');
        $this->assertSame(2, $owner->activeWillReceivers()->count());
        $this->assertFalse($owner->activeWillReceivers()->where('rank', 3)->exists());

        $response = $this->actingAs($owner)->get('/will-receivers');
        $response->assertOk();
        $response->assertSee('1 more saved than that', false);

        // Trimming via the form only touches ranks up to the Basic limit —
        // the excess rank-3 row is untouched either way.
        $this->assertSame(3, WillReceiver::where('user_id', $owner->id)->count());
    }
}
