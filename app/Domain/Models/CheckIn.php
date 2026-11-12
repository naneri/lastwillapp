<?php

namespace App\Domain\Models;

use App\Domain\Enums\CheckInStage;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property User $user
 * @property CarbonImmutable|mixed $confirmed_at
 */
#[Fillable(['stage', 'token', 'sent_at', 'expires_at', 'confirmed_at'])]
class CheckIn extends Model
{
    protected function casts(): array
    {
        return [
            'stage' => CheckInStage::class,
            'sent_at' => 'datetime',
            'expires_at' => 'datetime',
            'confirmed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed_at !== null;
    }

    public function isExpired(): bool
    {
        return ! $this->isConfirmed() && $this->expires_at->isPast();
    }
}
