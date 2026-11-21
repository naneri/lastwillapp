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
//
// withoutOverlapping()'s lock only gets released on a clean finish — a
// process hard-killed mid-run (e.g. a deploy) leaves it stuck until it
// expires on its own. The default expiry is 24h; that's much longer than
// these jobs' own cadence, so a single bad-timed kill could silently
// stall check-ins/escalations for the better part of a day. Setting the
// expiry to just over each job's own interval means a stuck lock
// self-heals within roughly one cycle instead.
Schedule::command('app:process-checkins')->hourly()->withoutOverlapping(90);
Schedule::command('app:process-escalations')->hourly()->withoutOverlapping(90);

// Drives the billing/plan lifecycle (SPEC.md §12) — daily is plenty of
// precision for a 7-day warning window and period-end plan changes.
Schedule::command('app:process-free-tier')->daily()->withoutOverlapping(1500);
Schedule::command('app:process-plan-changes')->daily()->withoutOverlapping(1500);
