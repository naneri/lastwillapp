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
        $hasRecord = filled($owner->record()->first()?->body);
        $hasBothWillreceivers = $owner->willreceivers()->count() === 2;

        if (! $hasRecord || ! $hasBothWillreceivers) {
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
            $this->sendCheckIn($owner, CheckInStage::Monthly, $now, $now->addDays(30));
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
        $willreceiver = $owner->willreceivers()->where('rank', $rank)->first();

        if (! $willreceiver) {
            return;
        }

        $reveal = $owner->reveals()->create([
            'willreceiver_id' => $willreceiver->id,
            'token' => Str::random(64),
            'sent_at' => $now,
        ]);

        Mail::to($willreceiver->email)->send(new RevealMail($owner, $willreceiver, $reveal));
    }

    /**
     * Called periodically by app:process-escalations. Escalates to
     * willreceiver #2 once #1's 3-day confirmation window has lapsed
     * without a "mark as received" (SPEC.md §5). #1's link/reveal stays
     * valid regardless (SPEC.md §10.1) — this only adds #2, never revokes.
     */
    public function processEscalations(?CarbonImmutable $now = null): void
    {
        $now = $now ?? CarbonImmutable::now();

        User::query()
            ->where('status', OwnerStatus::Disappeared)
            ->chunkById(100, function ($owners) use ($now) {
                foreach ($owners as $owner) {
                    $this->evaluateEscalation($owner, $now);
                }
            });
    }

    protected function evaluateEscalation(User $owner, CarbonImmutable $now): void
    {
        $firstReveal = $owner->reveals()->whereHas('willreceiver', fn ($q) => $q->where('rank', 1))->first();

        if (! $firstReveal || $firstReveal->isConfirmed()) {
            return;
        }

        if ($firstReveal->sent_at->addDays(3)->gt($now)) {
            return;
        }

        $secondAlreadySent = $owner->reveals()->whereHas('willreceiver', fn ($q) => $q->where('rank', 2))->exists();

        if ($secondAlreadySent) {
            return;
        }

        $this->triggerReveal($owner, rank: 2, now: $now);
    }
}
