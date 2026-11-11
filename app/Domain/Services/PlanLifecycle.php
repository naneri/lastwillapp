<?php

namespace App\Domain\Services;

use App\Domain\Enums\Plan;
use App\Domain\Mail\FreeTierExpiryWarningMail;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Mail;

/**
 * Drives the plan/billing side of an owner's account (SPEC.md §12),
 * separate from the check-in/escalation state machine in
 * CheckInLifecycle. Entry points called by the scheduler (see
 * routes/console.php): processFreeTierLifecycle() and
 * applyDuePlanChanges(). upgrade()/scheduleDowngrade() are called from the
 * Paddle webhook handler once Paddle confirms the relevant billing event.
 */
class PlanLifecycle
{
    /**
     * Upgrades take effect immediately (SPEC.md §12) — Paddle has already
     * billed the owner, prorated, by the time its webhook calls this.
     */
    public function upgrade(User $owner, Plan $plan): void
    {
        $owner->plan = $plan;
        $owner->pending_plan = null;
        $owner->plan_change_effective_at = null;
        $owner->save();
    }

    /**
     * Downgrades (including cancellation back to Free) take effect at the
     * end of the current paid period, not immediately (SPEC.md §12) — the
     * owner keeps their current tier's access until then.
     */
    public function scheduleDowngrade(User $owner, Plan $plan, CarbonImmutable $effectiveAt): void
    {
        $owner->pending_plan = $plan;
        $owner->plan_change_effective_at = $effectiveAt;
        $owner->save();
    }

    /**
     * Called periodically by app:process-plan-changes. Applies any
     * downgrade whose effective date has arrived.
     */
    public function applyDuePlanChanges(?CarbonImmutable $now = null): void
    {
        $now = $now ?? CarbonImmutable::now();

        User::query()
            ->whereNotNull('pending_plan')
            ->where('plan_change_effective_at', '<=', $now)
            ->chunkById(100, function ($owners) {
                foreach ($owners as $owner) {
                    $owner->plan = $owner->pending_plan;
                    $owner->pending_plan = null;
                    $owner->plan_change_effective_at = null;
                    $owner->save();
                }
            });
    }

    /**
     * Called periodically by app:process-free-tier. Warns Free owners 7
     * days before their one-time 100-day window ends (SPEC.md §12). Pausing
     * itself needs no action here — CheckInLifecycle checks
     * User::hasActiveAccess() directly, so an expired Free owner simply
     * stops being sent check-ins/escalations/reveals without a stored
     * "paused" flag.
     */
    public function processFreeTierLifecycle(?CarbonImmutable $now = null): void
    {
        $now = $now ?? CarbonImmutable::now();

        User::query()
            ->where('plan', Plan::Free)
            ->whereNull('free_tier_warning_sent_at')
            ->chunkById(100, function ($owners) use ($now) {
                foreach ($owners as $owner) {
                    if ($owner->freeTierWarningDueAt()->lte($now)) {
                        $owner->free_tier_warning_sent_at = $now;
                        $owner->save();

                        Mail::to($owner->email)->send(new FreeTierExpiryWarningMail($owner));
                    }
                }
            });
    }
}
