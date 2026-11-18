<?php

namespace Tests\Feature;

use App\Domain\Enums\CheckInStage;
use App\Domain\Enums\OwnerActionType;
use App\Domain\Enums\Plan;
use App\Domain\Models\OwnerActionLog;
use App\Domain\Services\CheckInLifecycle;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OwnerActionLogTest extends TestCase
{
    use RefreshDatabase;

    protected function makeActiveOwner(): User
    {
        $owner = User::factory()->create(['plan' => Plan::Basic]);
        $lifecycle = app(CheckInLifecycle::class);
        $lifecycle->initialize($owner);

        $owner->record()->create(['body' => 'See the lockbox in the hall closet.']);
        $owner->willReceivers()->create(['rank' => 1, 'email' => 'first@example.com']);

        $lifecycle->activateIfReady($owner);

        return $owner->fresh();
    }

    public function test_checking_in_from_the_dashboard_logs_the_action(): void
    {
        $owner = $this->makeActiveOwner();

        $this->actingAs($owner)
            ->withServerVariables(['REMOTE_ADDR' => '203.0.113.5', 'HTTP_USER_AGENT' => 'test-agent-checkin'])
            ->post('/checkin')
            ->assertRedirect(route('dashboard'));

        $this->assertSame(1, OwnerActionLog::count());

        $log = OwnerActionLog::first();
        $this->assertSame($owner->id, $log->user_id);
        $this->assertSame(OwnerActionType::CheckedIn, $log->action);
        $this->assertSame('203.0.113.5', $log->ip_address);
        $this->assertSame('test-agent-checkin', $log->user_agent);
    }

    public function test_pausing_logs_the_action(): void
    {
        $owner = $this->makeActiveOwner();

        $this->actingAs($owner)
            ->withServerVariables(['REMOTE_ADDR' => '203.0.113.6', 'HTTP_USER_AGENT' => 'test-agent-pause'])
            ->post('/checkin/pause')
            ->assertRedirect(route('dashboard'));

        $this->assertSame(1, OwnerActionLog::count());

        $log = OwnerActionLog::first();
        $this->assertSame($owner->id, $log->user_id);
        $this->assertSame(OwnerActionType::Paused, $log->action);
        $this->assertSame('203.0.113.6', $log->ip_address);
        $this->assertSame('test-agent-pause', $log->user_agent);
    }

    public function test_resuming_from_paused_logs_the_resumed_action(): void
    {
        $owner = $this->makeActiveOwner();
        app(CheckInLifecycle::class)->pause($owner);

        $this->actingAs($owner->fresh())
            ->withServerVariables(['REMOTE_ADDR' => '203.0.113.9', 'HTTP_USER_AGENT' => 'test-agent-resume'])
            ->post('/checkin')
            ->assertRedirect(route('dashboard'));

        $log = OwnerActionLog::first();
        $this->assertSame($owner->id, $log->user_id);
        $this->assertSame(OwnerActionType::Resumed, $log->action);
        $this->assertSame('203.0.113.9', $log->ip_address);
        $this->assertSame('test-agent-resume', $log->user_agent);
    }

    public function test_confirming_an_emailed_check_in_link_logs_the_action(): void
    {
        Mail::fake();

        $owner = $this->makeActiveOwner();
        $checkIn = $owner->checkIns()->create([
            'stage' => CheckInStage::Monthly,
            'token' => 'test-token-owner-action-log',
            'sent_at' => CarbonImmutable::now(),
            'expires_at' => CarbonImmutable::now()->addDays(3),
        ]);

        // Deliberately not authenticated — this route is reached straight
        // from an emailed link, no session involved.
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.7', 'HTTP_USER_AGENT' => 'test-agent-confirm'])
            ->get('/checkin/'.$checkIn->token)
            ->assertOk();

        $this->assertSame(1, OwnerActionLog::count());

        $log = OwnerActionLog::first();
        $this->assertSame($owner->id, $log->user_id);
        $this->assertSame(OwnerActionType::CheckInConfirmed, $log->action);
        $this->assertSame('203.0.113.7', $log->ip_address);
        $this->assertSame('test-agent-confirm', $log->user_agent);
    }

    public function test_an_already_confirmed_or_expired_link_logs_nothing(): void
    {
        $owner = $this->makeActiveOwner();
        $checkIn = $owner->checkIns()->create([
            'stage' => CheckInStage::Monthly,
            'token' => 'test-token-already-confirmed',
            'sent_at' => CarbonImmutable::now(),
            'expires_at' => CarbonImmutable::now()->addDays(3),
            'confirmed_at' => CarbonImmutable::now(),
        ]);

        $this->get('/checkin/'.$checkIn->token)->assertOk();

        $this->assertSame(0, OwnerActionLog::count());
    }
}
