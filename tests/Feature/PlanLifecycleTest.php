<?php

namespace Tests\Feature;

use App\Domain\Enums\Plan;
use App\Domain\Mail\FreeTierExpiryWarningMail;
use App\Domain\Services\PlanLifecycle;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PlanLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected PlanLifecycle $lifecycle;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lifecycle = app(PlanLifecycle::class);
    }

    public function test_free_owner_has_active_access_until_100_days_after_signup(): void
    {
        $owner = User::factory()->create(['plan' => Plan::Free]);

        $this->assertTrue($owner->hasActiveAccess(CarbonImmutable::now()->addDays(99)));
        $this->assertFalse($owner->hasActiveAccess(CarbonImmutable::now()->addDays(101)));
    }

    public function test_paid_owner_always_has_active_access(): void
    {
        $owner = User::factory()->create(['plan' => Plan::Basic]);

        $this->assertTrue($owner->hasActiveAccess(CarbonImmutable::now()->addYears(5)));
    }

    public function test_free_tier_warning_is_sent_once_7_days_before_expiry_and_not_resent(): void
    {
        Mail::fake();

        $owner = User::factory()->create(['plan' => Plan::Free]);
        $now = CarbonImmutable::now();

        $this->lifecycle->processFreeTierLifecycle($now->addDays(90));
        Mail::assertNothingQueued();
        $this->assertNull($owner->fresh()->free_tier_warning_sent_at);

        $this->lifecycle->processFreeTierLifecycle($now->addDays(93));
        Mail::assertQueued(FreeTierExpiryWarningMail::class, 1);
        $this->assertNotNull($owner->fresh()->free_tier_warning_sent_at);

        // Running it again later must not send a second warning.
        $this->lifecycle->processFreeTierLifecycle($now->addDays(99));
        Mail::assertQueued(FreeTierExpiryWarningMail::class, 1);
    }

    public function test_upgrade_takes_effect_immediately_and_clears_any_pending_downgrade(): void
    {
        $owner = User::factory()->create(['plan' => Plan::Pro]);
        $this->lifecycle->scheduleDowngrade($owner, Plan::Basic, CarbonImmutable::now()->addMonths(6));

        $this->lifecycle->upgrade($owner, Plan::Pro);

        $owner->refresh();
        $this->assertSame(Plan::Pro, $owner->plan);
        $this->assertNull($owner->pending_plan);
        $this->assertNull($owner->plan_change_effective_at);
    }

    public function test_scheduled_downgrade_only_applies_once_its_effective_date_arrives(): void
    {
        $owner = User::factory()->create(['plan' => Plan::Pro]);
        $now = CarbonImmutable::now();

        $this->lifecycle->scheduleDowngrade($owner, Plan::Basic, $now->addDays(30));

        $this->lifecycle->applyDuePlanChanges($now->addDays(10));
        $this->assertSame(Plan::Pro, $owner->fresh()->plan, 'too early to apply');

        $this->lifecycle->applyDuePlanChanges($now->addDays(30));
        $owner->refresh();
        $this->assertSame(Plan::Basic, $owner->plan);
        $this->assertNull($owner->pending_plan);
        $this->assertNull($owner->plan_change_effective_at);
    }
}
