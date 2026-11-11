<?php

namespace App\Domain\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['will_receiver_id', 'token', 'sent_at', 'confirmed_at'])]
class Reveal extends Model
{
    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'confirmed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function willReceiver(): BelongsTo
    {
        return $this->belongsTo(WillReceiver::class);
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed_at !== null;
    }
}
