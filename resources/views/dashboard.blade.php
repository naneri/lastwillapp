<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    @if (session('status') === 'checked-in')
        <div class="alert alert-success">
            {{ __("Thanks — you're confirmed. We'll check in again in 30 days.") }}
        </div>
    @elseif (session('status') === 'checkins-paused')
        <div class="alert alert-secondary">
            {{ __('Check-ins are paused. Resume any time from this page.') }}
        </div>
    @endif

    @if ($owner->plan === \App\Domain\Enums\Plan::Free && ! $owner->hasActiveAccess())
        <div class="alert alert-danger">
            Your free period ended on {{ $owner->freeTierEndsAt()->toFormattedDateString() }} —
            monitoring is paused until you
            <a href="{{ route('billing.show') }}">subscribe to a paid plan</a>.
        </div>
    @endif

    @if ($willReceiverCount > $willReceiverLimit)
        <div class="alert alert-warning">
            You have {{ $willReceiverCount }} trusted contacts but your plan only
            supports {{ $willReceiverLimit }} — the extra
            {{ Str::plural('one', $willReceiverCount - $willReceiverLimit) }} won't be
            notified. <a href="{{ route('will-receivers.edit') }}">Remove some</a> or
            <a href="{{ route('billing.show') }}">upgrade</a>.
        </div>
    @endif

    @php
        // Kept short (SPEC.md §4 has the full explanation): this label is the
        // big number in a small-box tile below, which forces nowrap.
        $statusLabel = match ($owner->status) {
            \App\Domain\Enums\OwnerStatus::Setup => ['Setup', 'secondary'],
            \App\Domain\Enums\OwnerStatus::Active => ['Active', 'success'],
            \App\Domain\Enums\OwnerStatus::AtRisk => ['At risk', 'warning'],
            \App\Domain\Enums\OwnerStatus::DailyRetry => ['Urgent', 'danger'],
            \App\Domain\Enums\OwnerStatus::Disappeared => ['Disappeared', 'dark'],
            \App\Domain\Enums\OwnerStatus::Paused => ['Paused', 'secondary'],
        };

        // next_checkin_due_at is dual-purposed (SPEC.md §4): while Active it's
        // when the next check-in will be SENT; while AtRisk/DailyRetry it's
        // when the currently open one EXPIRES. Either way, "days remaining
        // until that date" is a meaningful number to show here.
        $daysRemaining = $owner->next_checkin_due_at
            ? max(0, (int) ceil(($owner->next_checkin_due_at->timestamp - now()->timestamp) / 86400))
            : null;

        // The window length behind that date, so we can also show how much
        // of it has elapsed — fixed per stage (SPEC.md §4), so no extra query
        // is needed to find when the window started.
        $windowLength = match ($owner->status) {
            \App\Domain\Enums\OwnerStatus::Active => 30,
            \App\Domain\Enums\OwnerStatus::AtRisk => 7,
            \App\Domain\Enums\OwnerStatus::DailyRetry => 1,
            default => null,
        };

        $windowPercent = null;

        if ($windowLength && $owner->next_checkin_due_at) {
            $windowStart = $owner->next_checkin_due_at->copy()->subDays($windowLength);
            $totalSeconds = $owner->next_checkin_due_at->timestamp - $windowStart->timestamp;
            $elapsedSeconds = now()->timestamp - $windowStart->timestamp;
            $windowPercent = $totalSeconds > 0 ? min(100, max(0, ($elapsedSeconds / $totalSeconds) * 100)) : 0;
        }
    @endphp

    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box text-bg-{{ $statusLabel[1] }}">
                <div class="inner">
                    {{-- Status text varies in length ("Active" vs. "Disappeared"), unlike the
                         other tiles' numbers/short words — small-box's h3 is nowrap by default,
                         which overflows for the longer labels, so this one gets to wrap. --}}
                    <h3 class="fs-3" style="white-space: normal;">{{ $statusLabel[0] }}</h3>
                    <p>Status</p>
                </div>
                <i class="bi bi-shield-check small-box-icon"></i>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            @if ($owner->status === \App\Domain\Enums\OwnerStatus::Setup)
                <div class="small-box text-bg-secondary">
                    <div class="inner">
                        <h3>{{ ($hasRecord ? 1 : 0) + ($willReceiverCount >= 1 ? 1 : 0) }}/2</h3>
                        <p>Setup steps done</p>
                    </div>
                    <i class="bi bi-rocket-takeoff small-box-icon"></i>
                </div>
            @elseif ($owner->status === \App\Domain\Enums\OwnerStatus::Disappeared)
                <div class="small-box text-bg-dark">
                    <div class="inner">
                        <h3>!</h3>
                        <p>Action needed</p>
                    </div>
                    <i class="bi bi-exclamation-octagon small-box-icon"></i>
                </div>
            @elseif ($owner->status === \App\Domain\Enums\OwnerStatus::Paused)
                <div class="small-box text-bg-secondary">
                    <div class="inner">
                        <h3>Off</h3>
                        <p>Check-ins</p>
                    </div>
                    <i class="bi bi-pause-circle small-box-icon"></i>
                </div>
            @else
                <div class="small-box text-bg-info">
                    <div class="inner">
                        <h3>{{ $daysRemaining }}</h3>
                        <p>{{ Str::plural('day', $daysRemaining) }} until next check-in</p>
                    </div>
                    <i class="bi bi-calendar-check small-box-icon"></i>
                </div>
            @endif
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box text-bg-{{ $willReceiverCount > $willReceiverLimit ? 'warning' : 'primary' }}">
                <div class="inner">
                    <h3>{{ $willReceiverCount }}/{{ $willReceiverLimit }}</h3>
                    <p>Trusted contacts</p>
                </div>
                <i class="bi bi-people-fill small-box-icon"></i>
                <a href="{{ route('will-receivers.edit') }}" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                    Manage contacts <i class="bi bi-link-45deg"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            @php
                $planColor = match ($owner->plan) {
                    \App\Domain\Enums\Plan::Free => 'secondary',
                    \App\Domain\Enums\Plan::Basic => 'info',
                    \App\Domain\Enums\Plan::Pro => 'success',
                };
            @endphp
            <div class="small-box text-bg-{{ $planColor }}">
                <div class="inner">
                    <h3>{{ $owner->plan->label() }}</h3>
                    <p>Plan</p>
                </div>
                <i class="bi bi-credit-card-2-front small-box-icon"></i>
                <a href="{{ route('billing.show') }}" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                    Manage plan <i class="bi bi-link-45deg"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title mb-0">
                <i class="bi bi-shield-check me-1"></i>
                {{ __('Account status') }}
            </h3>

            {{-- ms-auto, not justify-content-between on the header: AdminLTE's
                 .card-header has a ::after clearfix pseudo-element that counts
                 as a trailing flex item, so space-between splits the gap in
                 two instead of pushing this flush right. --}}
            @if (in_array($owner->status, [\App\Domain\Enums\OwnerStatus::Active, \App\Domain\Enums\OwnerStatus::AtRisk, \App\Domain\Enums\OwnerStatus::DailyRetry], true))
                <button type="button" class="btn btn-warning btn-sm ms-auto" data-bs-toggle="modal" data-bs-target="#pause-modal">
                    {{ __('Pause check-ins') }}
                </button>
            @endif
        </div>
        <div class="card-body">
            @if ($owner->status === \App\Domain\Enums\OwnerStatus::Setup)
                <p class="text-muted mb-0">
                    Monitoring hasn't started yet. Finish the two steps below —
                    your 30-day check-in cycle begins as soon as both are done.
                </p>
            @elseif ($owner->status === \App\Domain\Enums\OwnerStatus::Paused)
                <p class="text-muted">
                    You've paused check-ins. No reminders will be sent, and
                    your trusted contacts won't be notified even if
                    something happens to you — monitoring stays off until
                    you resume it.
                </p>

                <form method="POST" action="{{ route('owner-checkin.store') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        {{ __('Resume check-ins') }}
                    </button>
                </form>
            @else
                @if ($owner->status === \App\Domain\Enums\OwnerStatus::Disappeared)
                    <p class="text-muted">
                        We weren't able to confirm you were OK, so your information may
                        already have been shared with your emergency contacts. If
                        you're fine, click below to stop any further sharing and
                        resume normal check-ins.
                    </p>
                @elseif ($owner->next_checkin_due_at)
                    <p class="text-muted mb-1">
                        Next check-in due: {{ $owner->next_checkin_due_at->timezone($owner->timezone)->format('F j, Y H:i') }}
                        ({{ $owner->timezone }})
                    </p>

                    @if ($windowPercent !== null)
                        <div class="progress mb-2" style="height: 6px;" role="progressbar" aria-valuenow="{{ round($windowPercent) }}" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar bg-{{ $statusLabel[1] }}" style="width: {{ $windowPercent }}%"></div>
                        </div>
                        <p class="text-muted small mb-3">
                            {{ $daysRemaining }} {{ Str::plural('day', $daysRemaining) }} remaining in this window
                        </p>
                    @endif
                @endif

                <form method="POST" action="{{ route('owner-checkin.store') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        {{ __('Check in now') }}
                        <i
                            class="bi bi-question-circle ms-1"
                            data-bs-toggle="tooltip"
                            data-bs-title="Confirms you're OK and resets your next check-in to 30 days from now."
                        ></i>
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="modal fade" id="pause-modal" tabindex="-1" aria-labelledby="pause-modal-label" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title h5" id="pause-modal-label">Pause check-ins?</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>
                        While paused, you won't get check-in reminders — and
                        your trusted contacts won't be notified either, even
                        if something happens to you. Monitoring stays off
                        until you come back and resume it yourself.
                    </p>
                    <p class="mb-0 text-muted small">
                        This doesn't affect your subscription or free trial —
                        both keep running as normal while paused.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="{{ route('owner-checkin.pause') }}">
                        @csrf
                        <button type="submit" class="btn btn-danger">Yes, pause check-ins</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if ($owner->status === \App\Domain\Enums\OwnerStatus::Setup)
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-list-check me-1"></i>
                    {{ __('Finish setup') }}
                </h3>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li>
                        {{ $hasRecord ? '✓' : '○' }}
                        <a href="{{ route('record.edit') }}">Write your record</a>
                    </li>
                    <li>
                        {{ $willReceiverCount >= 1 ? '✓' : '○' }}
                        <a href="{{ route('will-receivers.edit') }}">Add a trusted contact</a>
                    </li>
                </ul>
            </div>
        </div>
    @endif

    <div class="d-flex gap-3">
        <a href="{{ route('record.edit') }}" class="btn btn-success">Edit your record</a>
        <a href="{{ route('will-receivers.edit') }}" class="btn btn-success">Edit trusted contacts</a>
    </div>

    <script>
        // Bootstrap tooltips are opt-in — the data-bs-toggle attribute alone
        // doesn't activate them, unlike modals/dropdowns. Deferred to
        // DOMContentLoaded: this script renders as part of the page content,
        // before the layout's own closing <script src="bootstrap..."> tag,
        // so `bootstrap` isn't defined yet if called immediately.
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => new bootstrap.Tooltip(el));
        });
    </script>
</x-app-layout>
