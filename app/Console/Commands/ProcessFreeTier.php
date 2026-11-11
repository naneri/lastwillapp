<?php

namespace App\Console\Commands;

use App\Domain\Services\PlanLifecycle;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:process-free-tier')]
#[Description('Warn Free owners 7 days before their 100-day window ends (SPEC.md §12)')]
class ProcessFreeTier extends Command
{
    public function handle(PlanLifecycle $lifecycle): void
    {
        $lifecycle->processFreeTierLifecycle();
    }
}
