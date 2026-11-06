<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Drives the owner check-in/escalation state machine (SPEC.md §4, §5).
// Hourly gives reasonable precision on the day-granularity deadlines
// without needing per-owner timezone-aware scheduling in v1.
Schedule::command('app:process-checkins')->hourly()->withoutOverlapping();
Schedule::command('app:process-escalations')->hourly()->withoutOverlapping();
