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

    /**
     * How much visible Record text (MaxVisibleTextLength) this plan
     * allows.
     */
    public function recordCharacterLimit(): int
    {
        return match ($this) {
            self::Free => 1_000,
            self::Basic, self::Pro => 30_000,
        };
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
