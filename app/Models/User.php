<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Domain\Enums\OwnerStatus;
use App\Domain\Enums\Plan;
use App\Domain\Models\CheckIn;
use App\Domain\Models\Record;
use App\Domain\Models\Reveal;
use App\Domain\Models\WillReceiver;
use Carbon\CarbonImmutable;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property mixed $status
 * @property mixed $id
 * @property CarbonImmutable|mixed $disappeared_at
 */
#[Fillable(['name', 'email', 'password', 'timezone'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => OwnerStatus::class,
            'next_checkin_due_at' => 'datetime',
            'disappeared_at' => 'datetime',
            'plan' => Plan::class,
            'pending_plan' => Plan::class,
            'plan_change_effective_at' => 'datetime',
            'free_tier_warning_sent_at' => 'datetime',
        ];
    }

    public function record(): HasOne
    {
        return $this->hasOne(Record::class);
    }

    public function willReceivers(): HasMany
    {
        return $this->hasMany(WillReceiver::class)->orderBy('rank');
    }

    /**
     * Willreceivers this owner's current plan actually allows (SPEC.md §12)
     * — excess receivers from a downgrade stay in the table but drop out of
     * this scope until the owner trims down to the new limit.
     */
    public function activeWillReceivers(): HasMany
    {
        return $this->willReceivers()->where('rank', '<=', $this->plan->willReceiverLimit());
    }

    public function checkIns(): HasMany
    {
        return $this->hasMany(CheckIn::class);
    }

    public function reveals(): HasMany
    {
        return $this->hasMany(Reveal::class);
    }

    public function freeTierEndsAt(): CarbonImmutable
    {
        return CarbonImmutable::parse($this->created_at)->addDays(100);
    }

    public function freeTierWarningDueAt(): CarbonImmutable
    {
        return $this->freeTierEndsAt()->subDays(7);
    }

    /**
     * Whether check-in/escalation/reveal monitoring should run for this
     * owner right now. Paid plans always have access; a Free owner only has
     * access until their one-time 100-day window ends (SPEC.md §12).
     */
    public function hasActiveAccess(?CarbonImmutable $now = null): bool
    {
        if ($this->plan !== Plan::Free) {
            return true;
        }

        $now = $now ?? CarbonImmutable::now();

        return $now->lt($this->freeTierEndsAt());
    }
}
