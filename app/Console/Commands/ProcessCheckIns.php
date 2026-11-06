<?php

namespace App\Console\Commands;

use App\Services\CheckInLifecycle;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:process-checkins')]
#[Description('Send due check-in emails and escalate any missed ones (SPEC.md §4)')]
class ProcessCheckIns extends Command
{
    public function handle(CheckInLifecycle $lifecycle): void
    {
        $lifecycle->processDueCheckIns();
    }
}
