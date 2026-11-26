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

    public function test_free_owner_can_save_their_one_willreceiver(): void
    {
        $owner = User::factory()->create(['plan' => Plan::Free]);

        $response = $this->actingAs($owner)->put('/will-receivers/1', [
            'email' => 'first@gmail.com',
        ]);

        $response->assertRedirect(route('will-receivers.index'));
        $this->assertSame(1, $owner->willReceivers()->count());
        $this->assertSame('first@gmail.com', $owner->willReceivers()->first()->email);
    }

    public function test_free_owner_cannot_edit_or_save_a_rank_beyond_their_limit(): void
    {
        $owner = User::factory()->create(['plan' => Plan::Free]);

        $this->actingAs($owner)->get('/will-receivers/2/edit')->assertNotFound();
        $this->actingAs($owner)->put('/will-receivers/2', ['email' => 'second@gmail.com'])->assertNotFound();
        $this->assertSame(0, $owner->willReceivers()->count());
    }

    public function test_basic_owner_can_save_two_willreceivers(): void
    {
        $owner = User::factory()->create(['plan' => Plan::Basic]);

        $this->actingAs($owner)->put('/will-receivers/1', ['email' => 'first@gmail.com'])
            ->assertRedirect(route('will-receivers.index'));
        $this->actingAs($owner)->put('/will-receivers/2', ['email' => 'second@gmail.com'])
            ->assertRedirect(route('will-receivers.index'));

        $this->assertSame(2, $owner->willReceivers()->count());
    }

    public function test_pro_owner_can_save_three_willreceivers_with_custom_messages(): void
    {
        $owner = User::factory()->create(['plan' => Plan::Pro]);

        $this->actingAs($owner)->put('/will-receivers/1', ['email' => 'first@gmail.com', 'message' => 'For you, Alice.']);
        $this->actingAs($owner)->put('/will-receivers/2', ['email' => 'second@gmail.com', 'message' => 'For you, Bob.']);
        $this->actingAs($owner)->put('/will-receivers/3', ['email' => 'third@gmail.com']);

        $this->assertSame(3, $owner->willReceivers()->count());
        $this->assertSame('For you, Alice.', $owner->willReceivers()->where('rank', 1)->first()->message);
        $this->assertSame('For you, Bob.', $owner->willReceivers()->where('rank', 2)->first()->message);
        $this->assertNull($owner->willReceivers()->where('rank', 3)->first()->message);
    }

    public function test_basic_owner_cannot_set_a_custom_message_even_if_posted(): void
    {
        $owner = User::factory()->create(['plan' => Plan::Basic]);

        $this->actingAs($owner)->put('/will-receivers/1', [
            'email' => 'first@gmail.com',
            'message' => 'Sneaky message',
        ])->assertRedirect(route('will-receivers.index'));

        $this->assertNull($owner->willReceivers()->where('rank', 1)->first()->message);
    }

    public function test_duplicate_email_against_another_rank_is_rejected(): void
    {
        $owner = User::factory()->create(['plan' => Plan::Basic]);
        $owner->willReceivers()->create(['rank' => 1, 'email' => 'same@gmail.com']);

        $response = $this->actingAs($owner)->from('/will-receivers/2/edit')->put('/will-receivers/2', [
            'email' => 'same@gmail.com',
        ]);

        $response->assertRedirect('/will-receivers/2/edit');
        $response->assertSessionHasErrors('email');
        $this->assertSame(1, $owner->willReceivers()->count());
    }

    public function test_owner_cannot_add_their_own_email_as_a_trusted_contact(): void
    {
        $owner = User::factory()->create(['plan' => Plan::Basic, 'email' => 'owner@example.com']);

        $response = $this->actingAs($owner)->from('/will-receivers/1/edit')->put('/will-receivers/1', [
            'email' => 'Owner@Example.com',
        ]);

        $response->assertRedirect('/will-receivers/1/edit');
        $response->assertSessionHasErrors('email');
        $this->assertSame(0, $owner->willReceivers()->count());
    }

    public function test_saving_an_optional_contact_with_a_blank_email_is_rejected(): void
    {
        $owner = User::factory()->create(['plan' => Plan::Basic]);
        $owner->willReceivers()->create(['rank' => 1, 'email' => 'first@example.com']);
        $owner->willReceivers()->create(['rank' => 2, 'email' => 'second@example.com']);

        $response = $this->actingAs($owner)->from('/will-receivers/2/edit')->put('/will-receivers/2', ['email' => '']);

        $response->assertRedirect('/will-receivers/2/edit');
        $response->assertSessionHasErrors('email');
        $this->assertSame(2, $owner->willReceivers()->count(), 'the existing contact must not be deleted by a blank-email save');
    }

    public function test_deleting_an_optional_contact_removes_it(): void
    {
        $owner = User::factory()->create(['plan' => Plan::Basic]);
        $owner->willReceivers()->create(['rank' => 1, 'email' => 'first@example.com']);
        $owner->willReceivers()->create(['rank' => 2, 'email' => 'second@example.com']);

        $this->actingAs($owner)->delete('/will-receivers/2')
            ->assertRedirect(route('will-receivers.index'));

        $this->assertSame(1, $owner->willReceivers()->count());
        $this->assertFalse($owner->willReceivers()->where('rank', 2)->exists());
    }

    public function test_the_first_contact_cannot_be_deleted(): void
    {
        $owner = User::factory()->create(['plan' => Plan::Basic]);
        $owner->willReceivers()->create(['rank' => 1, 'email' => 'first@example.com']);

        $this->actingAs($owner)->delete('/will-receivers/1')->assertNotFound();

        $this->assertSame(1, $owner->willReceivers()->count());
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

        // The excess rank-3 row sits beyond the Basic limit and isn't even
        // reachable through the edit routes — it's untouched either way.
        $this->actingAs($owner)->get('/will-receivers/3/edit')->assertNotFound();
        $this->assertSame(3, WillReceiver::where('user_id', $owner->id)->count());
    }

    public function test_index_lists_an_unfilled_rank_as_details_required(): void
    {
        $owner = User::factory()->create(['plan' => Plan::Basic]);
        $owner->willReceivers()->create(['rank' => 1, 'email' => 'first@example.com']);

        $response = $this->actingAs($owner)->get('/will-receivers');

        $response->assertOk();
        $response->assertSee('Details required');
    }
}
