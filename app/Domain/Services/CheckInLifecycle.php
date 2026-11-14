<?php

namespace App\Domain\Services;

use App\Domain\Enums\CheckInStage;
use App\Domain\Enums\OwnerStatus;
use App\Domain\Mail\CheckInMail;
use App\Domain\Mail\RevealMail;
use App\Domain\Models\CheckIn;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Drives the owner check-in / escalation state machine described in
 * SPEC.md §4 and §5. Two entry points are called by the scheduler
 * (see routes/console.php): processDueCheckIns() and processEscalations().
 * confirm()/recover() are called from the owner- and receiver-facing
 * controllers.
 */
class CheckInLifecycle
{
    /**
     * A new owner starts in Setup (SPEC.md §3) — no check-in clock runs
     * until they finish setting up their record and both willreceivers;
     * see activateIfReady().
     */
    public function initialize(User $user): void
    {
        $user->forceFill([
            'status' => OwnerStatus::Setup,
            'next_checkin_due_at' => null,
            'daily_retry_day' => null,
        ])->save();
    }

    /**
     * Called after saving the record or the willreceivers. Moves a Setup
     * owner to Active — starting the 30-day check-in clock from now — once
     * both the record and both willreceivers are present.
     */
    public function activateIfReady(User $owner): void
    {
        if ($owner->status !== OwnerStatus::Setup) {
            return;
        }

        // Query fresh rather than via $owner->record — a cached relation
        // (e.g. loaded as null earlier in the same request) would go stale
        // the moment the record is actually saved.
        // SPEC.md §10.6 — activating only ever needs 1 willreceiver,
        // regardless of plan, so a Free owner (1 slot) can still activate.
        $hasRecord = filled($owner->record()->first()?->body);
        $hasWillReceiver = $owner->willReceivers()->count() >= 1;

        if (! $hasRecord || ! $hasWillReceiver) {
            return;
        }

        $owner->status = OwnerStatus::Active;
        $owner->next_checkin_due_at = CarbonImmutable::now()->addDays(30);
        $owner->save();
    }

    /**
     * Called periodically by app:process-checkins. For every owner being
     * actively monitored: escalate any expired, unconfirmed check-in, or
     * send the next monthly check-in if none is currently pending and it's
     * due. Setup owners have no clock running yet; Disappeared owners are
     * handled by processEscalations() instead.
     */
    public function processDueCheckIns(?CarbonImmutable $now = null): void
    {
        $now = $now ?? CarbonImmutable::now();

        User::query()
            ->whereIn('status', [OwnerStatus::Active, OwnerStatus::AtRisk, OwnerStatus::DailyRetry])
            ->chunkById(100, function ($owners) use ($now) {
                foreach ($owners as $owner) {
                    $this->evaluateOwner($owner, $now);
                }
            });
    }

    protected function evaluateOwner(User $owner, CarbonImmutable $now): void
    {
        // A Free owner past their one-time 100-day window (SPEC.md §12)
        // gets no further check-ins/escalations until they subscribe.
        if (! $owner->hasActiveAccess($now)) {
            return;
        }

        $openCheckIn = $owner->checkIns()->whereNull('confirmed_at')->latest('sent_at')->first();

        if ($openCheckIn) {
            if ($openCheckIn->expires_at->lte($now)) {
                $this->escalate($owner, $now);
            }

            return;
        }

        if ($owner->status === OwnerStatus::Active
            && $owner->next_checkin_due_at !== null
            && $owner->next_checkin_due_at->lte($now)) {
            $this->sendCheckIn($owner, CheckInStage::Monthly, $now, $now->addDays(3));
        }
    }

    protected function escalate(User $owner, CarbonImmutable $now): void
    {
        match ($owner->status) {
            OwnerStatus::Active => $this->moveToAtRisk($owner, $now),
            OwnerStatus::AtRisk => $this->moveToDailyRetry($owner, $now),
            OwnerStatus::DailyRetry => $this->continueOrDeclareDisappeared($owner, $now),
            OwnerStatus::Setup, OwnerStatus::Disappeared => null,
        };
    }

    protected function moveToAtRisk(User $owner, CarbonImmutable $now): void
    {
        $owner->status = OwnerStatus::AtRisk;
        $owner->save();

        $this->sendCheckIn($owner, CheckInStage::AtRisk, $now, $now->addDays(7));
    }

    protected function moveToDailyRetry(User $owner, CarbonImmutable $now): void
    {
        $owner->status = OwnerStatus::DailyRetry;
        $owner->daily_retry_day = 1;
        $owner->save();

        $this->sendCheckIn($owner, CheckInStage::DailyRetry, $now, $now->addDay());
    }

    protected function continueOrDeclareDisappeared(User $owner, CarbonImmutable $now): void
    {
        if ($owner->daily_retry_day < 7) {
            $owner->daily_retry_day++;
            $owner->save();

            $this->sendCheckIn($owner, CheckInStage::DailyRetry, $now, $now->addDay());

            return;
        }

        $owner->status = OwnerStatus::Disappeared;
        $owner->disappeared_at = $now;
        $owner->save();

        $this->triggerReveal($owner, rank: 1, now: $now);
    }

    protected function sendCheckIn(User $owner, CheckInStage $stage, CarbonImmutable $now, CarbonImmutable $expiresAt): void
    {
        $checkIn = $owner->checkIns()->create([
            'stage' => $stage,
            'token' => Str::random(64),
            'sent_at' => $now,
            'expires_at' => $expiresAt,
        ]);

        $owner->next_checkin_due_at = $expiresAt;
        $owner->save();

        Mail::to($owner->email)->send(new CheckInMail($owner, $checkIn));
    }

    /**
     * Confirm a specific emailed check-in token (clicked from the email).
     */
    public function confirmToken(CheckIn $checkIn): void
    {
        if ($checkIn->isConfirmed()) {
            return;
        }

        $checkIn->confirmed_at = CarbonImmutable::now();
        $checkIn->save();

        $this->resetToActive($checkIn->user);
    }

    /**
     * Owner checks in directly from the app (works from any non-disappeared
     * state, and also lets a disappeared owner self-recover — see
     * SPEC.md §6 and §10.5).
     */
    public function recover(User $owner): void
    {
        $openCheckIn = $owner->checkIns()->whereNull('confirmed_at')->latest('sent_at')->first();

        if ($openCheckIn) {
            $openCheckIn->confirmed_at = CarbonImmutable::now();
            $openCheckIn->save();
        }

        $this->resetToActive($owner);
    }

    protected function resetToActive(User $owner): void
    {
        $owner->status = OwnerStatus::Active;
        $owner->daily_retry_day = null;
        $owner->next_checkin_due_at = CarbonImmutable::now()->addDays(30);
        $owner->save();
    }

    protected function triggerReveal(User $owner, int $rank, CarbonImmutable $now): void
    {
        // Scoped to activeWillReceivers so a receiver excluded by a
        // downgrade (SPEC.md §12) never gets revealed to.
        $willReceiver = $owner->activeWillReceivers()->where('rank', $rank)->first();

        if (! $willReceiver) {
            return;
        }

        $reveal = $owner->reveals()->create([
            'will_receiver_id' => $willReceiver->id,
            'token' => Str::random(64),
            'sent_at' => $now,
        ]);

        Mail::to($willReceiver->email)->send(new RevealMail($owner, $willReceiver, $reveal));
    }

    /**
     * Called periodically by app:process-escalations. Cascades through the
     * owner's willreceivers in rank order once each one's 3-day
     * confirmation window has lapsed without a "mark as received"
     * (SPEC.md §5) — up to however many ranks their plan allows (1 for
     * Free, 2 for Basic, 3 for Pro). Every earlier receiver's link/reveal
     * stays valid regardless (SPEC.md §10.1) — this only adds the next
     * rank, never revokes an earlier one.
     */
    public function processEscalations(?CarbonImmutable $now = null): void
    {
        $now = $now ?? CarbonImmutable::now();

        User::query()
            ->where('status', OwnerStatus::Disappeared)
            ->chunkById(100, function ($owners) use ($now) {
                foreach ($owners as $owner) {
                    if ($owner->hasActiveAccess($now)) {
                        $this->evaluateEscalation($owner, $now);
                    }
                }
            });
    }

    protected function evaluateEscalation(User $owner, CarbonImmutable $now): void
    {
        $limit = $owner->plan->willReceiverLimit();

        $latestReveal = $owner->reveals()
            ->whereHas('willReceiver', fn ($q) => $q->where('rank', '<=', $limit))
            ->get()
            ->sortByDesc(fn ($reveal) => $reveal->willReceiver->rank)
            ->first();

        if (! $latestReveal || $latestReveal->isConfirmed()) {
            return;
        }

        if ($latestReveal->sent_at->addDays(3)->gt($now)) {
            return;
        }

        $nextRank = $latestReveal->willReceiver->rank + 1;

        if ($nextRank > $limit) {
            return;
        }

        $this->triggerReveal($owner, rank: $nextRank, now: $now);
    }
}
