<?php

namespace App\Console\Commands;

use App\Domain\Services\CheckInLifecycle;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:process-escalations')]
#[Description('Escalate reveals to the second willreceiver once overdue (SPEC.md §5)')]
class ProcessEscalations extends Command
{
    public function handle(CheckInLifecycle $lifecycle): void
    {
        $lifecycle->processEscalations();
    }
}
