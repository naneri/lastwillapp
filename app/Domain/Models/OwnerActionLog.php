<?php

namespace App\Domain\Models;

use App\Domain\Enums\OwnerActionType;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An append-only audit trail of owner-initiated actions on the check-in
 * lifecycle (confirming via an emailed link, the in-app "check in now"
 * button, pausing) — kept indefinitely so a disputed "I never clicked
 * that" claim can be checked against who/when/from where.
 */
#[Fillable(['user_id', 'action', 'ip_address', 'user_agent'])]
class OwnerActionLog extends Model
{
    protected function casts(): array
    {
        return [
            'action' => OwnerActionType::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
