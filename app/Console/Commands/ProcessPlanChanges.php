<?php

namespace App\Console\Commands;

use App\Domain\Services\PlanLifecycle;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:process-plan-changes')]
#[Description('Apply any downgrade whose effective date has arrived (SPEC.md §12)')]
class ProcessPlanChanges extends Command
{
    public function handle(PlanLifecycle $lifecycle): void
    {
        $lifecycle->applyDuePlanChanges();
    }
}
