<?php

namespace Tests\Feature;

use App\Domain\Enums\OwnerStatus;
use App\Domain\Enums\Plan;
use App\Domain\Mail\CheckInMail;
use App\Domain\Mail\RevealMail;
use App\Domain\Models\WillReceiver;
use App\Domain\Services\CheckInLifecycle;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CheckInLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected CheckInLifecycle $lifecycle;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lifecycle = app(CheckInLifecycle::class);
    }

    /**
     * Returns an owner who has fully finished setup (record + both
     * willreceivers), so activateIfReady() has already moved them from
     * SETUP to ACTIVE — the starting point for most check-in tests below.
     * On Basic (2 willreceiver slots) since several of these tests exercise
     * escalation to a second willreceiver.
     */
    protected function makeOwnerWithReceivers(): User
    {
        $owner = User::factory()->create(['plan' => Plan::Basic]);
        $this->lifecycle->initialize($owner);

        $owner->record()->create(['body' => 'See the lockbox in the hall closet.']);
        $owner->willReceivers()->create(['rank' => 1, 'email' => 'first@example.com']);
        $owner->willReceivers()->create(['rank' => 2, 'email' => 'second@example.com']);

        $this->lifecycle->activateIfReady($owner);

        return $owner->fresh();
    }

    public function test_initialize_sets_setup_status_with_no_due_date(): void
    {
        $owner = User::factory()->create();

        $this->lifecycle->initialize($owner);
        $owner->refresh();

        $this->assertSame(OwnerStatus::Setup, $owner->status);
        $this->assertNull($owner->next_checkin_due_at);
    }

    public function test_owner_is_not_activated_until_record_and_at_least_one_willreceiver_are_set(): void
    {
        $owner = User::factory()->create();
        $this->lifecycle->initialize($owner);

        $owner->willReceivers()->create(['rank' => 1, 'email' => 'first@example.com']);
        $this->lifecycle->activateIfReady($owner);
        $this->assertSame(OwnerStatus::Setup, $owner->fresh()->status, 'still missing the record');

        $owner->record()->create(['body' => 'See the lockbox in the hall closet.']);
        $this->lifecycle->activateIfReady($owner);

        $owner->refresh();
        $this->assertSame(OwnerStatus::Active, $owner->status);
        $this->assertNotNull($owner->next_checkin_due_at);
        $this->assertTrue($owner->next_checkin_due_at->isSameDay(CarbonImmutable::now()->addDays(30)));
    }

    public function test_monthly_check_in_is_not_sent_before_it_is_due(): void
    {
        Mail::fake();

        $owner = $this->makeOwnerWithReceivers();

        $this->lifecycle->processDueCheckIns(CarbonImmutable::now()->addDays(10));

        Mail::assertNothingQueued();
        $this->assertSame(OwnerStatus::Active, $owner->fresh()->status);
    }

    public function test_missing_monthly_checkin_moves_owner_to_at_risk(): void
    {
        Mail::fake();

        $owner = $this->makeOwnerWithReceivers();
        $now = CarbonImmutable::now();

        // Due date arrives: this sends the monthly email, it does not yet escalate.
        $this->lifecycle->processDueCheckIns($now->addDays(30));
        $this->assertSame(OwnerStatus::Active, $owner->fresh()->status);
        Mail::assertQueued(CheckInMail::class, 1);

        // That monthly check-in's deadline passes unconfirmed: now it escalates.
        $this->lifecycle->processDueCheckIns($now->addDays(60));

        $this->assertSame(OwnerStatus::AtRisk, $owner->fresh()->status);
        Mail::assertQueued(CheckInMail::class, 2);
    }

    public function test_full_silence_leads_to_disappeared_and_first_reveal(): void
    {
        Mail::fake();

        $owner = $this->makeOwnerWithReceivers();
        $now = CarbonImmutable::now();

        $this->lifecycle->processDueCheckIns($now->addDays(30)); // sends monthly
        $this->lifecycle->processDueCheckIns($now->addDays(60)); // missed -> at_risk
        $this->lifecycle->processDueCheckIns($now->addDays(67)); // missed -> daily_retry day 1

        $owner->refresh();
        $this->assertSame(OwnerStatus::DailyRetry, $owner->status);
        $this->assertSame(1, $owner->daily_retry_day);

        // Miss all remaining 6 daily retries.
        for ($day = 2; $day <= 7; $day++) {
            $this->lifecycle->processDueCheckIns($now->addDays(67 + $day - 1));
        }

        // One more miss pushes past day 7 -> disappeared.
        $this->lifecycle->processDueCheckIns($now->addDays(67 + 7));

        $owner->refresh();
        $this->assertSame(OwnerStatus::Disappeared, $owner->status);
        $this->assertNotNull($owner->disappeared_at);

        $this->assertSame(1, $owner->reveals()->count());
        Mail::assertQueued(RevealMail::class, 1);
    }

    public function test_confirming_a_check_in_token_resets_owner_to_active(): void
    {
        Mail::fake();

        $owner = $this->makeOwnerWithReceivers();
        $now = CarbonImmutable::now();

        $this->lifecycle->processDueCheckIns($now->addDays(30));
        $this->lifecycle->processDueCheckIns($now->addDays(60)); // at_risk

        $checkIn = $owner->fresh()->checkIns()->whereNull('confirmed_at')->latest('sent_at')->first();

        $response = $this->get('/checkin/'.$checkIn->token);
        $response->assertOk();

        $owner->refresh();
        $this->assertSame(OwnerStatus::Active, $owner->status);

        $latestCheckIn = $owner->checkIns()->latest('sent_at')->first();
        $this->assertNotNull($latestCheckIn->confirmed_at);
    }

    public function test_expired_check_in_link_does_not_confirm(): void
    {
        Mail::fake();

        $owner = $this->makeOwnerWithReceivers();
        $now = CarbonImmutable::now();

        $this->lifecycle->processDueCheckIns($now->addDays(30));

        $checkIn = $owner->fresh()->checkIns()->whereNull('confirmed_at')->latest('sent_at')->first();
        $checkIn->expires_at = CarbonImmutable::now()->subMinute();
        $checkIn->save();

        $response = $this->get('/checkin/'.$checkIn->token);
        $response->assertOk();

        $this->assertNull($checkIn->fresh()->confirmed_at);
    }

    public function test_owner_can_self_recover_from_disappeared_via_in_app_checkin(): void
    {
        Mail::fake();

        $owner = $this->makeOwnerWithReceivers();
        $now = CarbonImmutable::now();

        $this->lifecycle->processDueCheckIns($now->addDays(30));
        $this->lifecycle->processDueCheckIns($now->addDays(60));
        $this->lifecycle->processDueCheckIns($now->addDays(67));
        for ($day = 2; $day <= 7; $day++) {
            $this->lifecycle->processDueCheckIns($now->addDays(67 + $day - 1));
        }
        $this->lifecycle->processDueCheckIns($now->addDays(67 + 7));

        $owner->refresh();
        $this->assertSame(OwnerStatus::Disappeared, $owner->status);

        $response = $this->actingAs($owner)->post('/checkin');
        $response->assertRedirect(route('dashboard'));

        $owner->refresh();
        $this->assertSame(OwnerStatus::Active, $owner->status);
    }

    public function test_second_willreceiver_is_notified_after_first_misses_three_day_window(): void
    {
        Mail::fake();

        $owner = $this->makeOwnerWithReceivers();
        $now = CarbonImmutable::now();

        $owner->status = OwnerStatus::Disappeared;
        $owner->disappeared_at = $now;
        $owner->save();

        // Directly exercise the escalation path, as if the first reveal
        // had already gone out 4 days ago with no confirmation.
        $first = WillReceiver::where('user_id', $owner->id)->where('rank', 1)->first();
        $owner->reveals()->create([
            'will_receiver_id' => $first->id,
            'token' => 'test-token-first',
            'sent_at' => $now->subDays(4),
        ]);

        $this->lifecycle->processEscalations($now);

        $this->assertSame(2, $owner->reveals()->count());
        Mail::assertQueued(RevealMail::class, 1);
    }

    public function test_second_willreceiver_is_not_notified_if_first_already_confirmed(): void
    {
        Mail::fake();

        $owner = $this->makeOwnerWithReceivers();
        $now = CarbonImmutable::now();

        $owner->status = OwnerStatus::Disappeared;
        $owner->disappeared_at = $now;
        $owner->save();

        $first = WillReceiver::where('user_id', $owner->id)->where('rank', 1)->first();
        $owner->reveals()->create([
            'will_receiver_id' => $first->id,
            'token' => 'test-token-first',
            'sent_at' => $now->subDays(4),
            'confirmed_at' => $now->subDays(3),
        ]);

        $this->lifecycle->processEscalations($now);

        $this->assertSame(1, $owner->reveals()->count());
        Mail::assertNothingQueued();
    }

    public function test_free_owner_past_their_100_day_window_receives_no_further_check_ins(): void
    {
        Mail::fake();

        $owner = User::factory()->create(['plan' => Plan::Free]);
        $this->lifecycle->initialize($owner);
        $owner->record()->create(['body' => 'See the lockbox in the hall closet.']);
        $owner->willReceivers()->create(['rank' => 1, 'email' => 'first@example.com']);
        $this->lifecycle->activateIfReady($owner);

        $now = CarbonImmutable::now();

        // Due date would normally arrive at +30 days, but the owner's
        // 100-day free window already lapsed by then relative to +101.
        $this->lifecycle->processDueCheckIns($now->addDays(101));

        Mail::assertNothingQueued();
        $this->assertSame(OwnerStatus::Active, $owner->fresh()->status);
    }

    public function test_pro_owner_escalates_through_all_three_willreceivers_in_order(): void
    {
        Mail::fake();

        $owner = User::factory()->create(['plan' => Plan::Pro]);
        $this->lifecycle->initialize($owner);
        $owner->record()->create(['body' => 'See the lockbox in the hall closet.']);
        $owner->willReceivers()->create(['rank' => 1, 'email' => 'first@example.com']);
        $owner->willReceivers()->create(['rank' => 2, 'email' => 'second@example.com']);
        $owner->willReceivers()->create(['rank' => 3, 'email' => 'third@example.com']);
        $this->lifecycle->activateIfReady($owner);

        $now = CarbonImmutable::now();
        $owner->status = OwnerStatus::Disappeared;
        $owner->disappeared_at = $now;
        $owner->save();

        $first = WillReceiver::where('user_id', $owner->id)->where('rank', 1)->first();
        $owner->reveals()->create([
            'will_receiver_id' => $first->id,
            'token' => 'test-token-first',
            'sent_at' => $now->subDays(4),
        ]);

        // First escalation: #1's window lapsed -> #2 gets notified.
        $this->lifecycle->processEscalations($now);
        $this->assertSame(2, $owner->reveals()->count());

        // #2's window also lapses -> #3 gets notified.
        $second = WillReceiver::where('user_id', $owner->id)->where('rank', 2)->first();
        $owner->reveals()->where('will_receiver_id', $second->id)->update(['sent_at' => $now->subDays(4)]);

        $this->lifecycle->processEscalations($now);
        $this->assertSame(3, $owner->reveals()->count());
        Mail::assertQueued(RevealMail::class, 2);

        // #3 is the last rank Pro allows — no further escalation possible.
        $third = WillReceiver::where('user_id', $owner->id)->where('rank', 3)->first();
        $owner->reveals()->where('will_receiver_id', $third->id)->update(['sent_at' => $now->subDays(4)]);

        $this->lifecycle->processEscalations($now);
        $this->assertSame(3, $owner->reveals()->count());
    }

    public function test_reveal_link_stays_valid_after_owner_recovers(): void
    {
        Mail::fake();

        $owner = $this->makeOwnerWithReceivers();
        $now = CarbonImmutable::now();

        $owner->status = OwnerStatus::Disappeared;
        $owner->disappeared_at = $now;
        $owner->save();

        $first = WillReceiver::where('user_id', $owner->id)->where('rank', 1)->first();
        $reveal = $owner->reveals()->create([
            'will_receiver_id' => $first->id,
            'token' => 'test-token-reveal',
            'sent_at' => $now,
        ]);

        $this->lifecycle->recover($owner);

        $response = $this->get('/reveal/'.$reveal->token);
        $response->assertOk();
    }
}
