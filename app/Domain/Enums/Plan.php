<?php

namespace App\Domain\Enums;

enum Plan: string
{
    case Free = 'free';
    case Basic = 'basic';
    case Pro = 'pro';

    /**
     * How many willreceivers this plan allows (SPEC.md §12).
     */
    public function willReceiverLimit(): int
    {
        return match ($this) {
            self::Free => 1,
            self::Basic => 2,
            self::Pro => 3,
        };
    }

    public function allowsCustomMessages(): bool
    {
        return $this === self::Pro;
    }

    public function label(): string
    {
        return match ($this) {
            self::Free => 'Free',
            self::Basic => 'Basic',
            self::Pro => 'Pro',
        };
    }
}
