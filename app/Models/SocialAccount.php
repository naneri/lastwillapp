<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Links one external identity provider (Google, etc.) to a user. A user
 * can have several of these, one per provider.
 */
#[Fillable(['user_id', 'provider', 'provider_id'])]
class SocialAccount extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
