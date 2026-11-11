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

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            @php
                $statusLabel = match ($owner->status) {
                    \App\Domain\Enums\OwnerStatus::Setup => ['Setup', 'secondary'],
                    \App\Domain\Enums\OwnerStatus::Active => ['Active', 'success'],
                    \App\Domain\Enums\OwnerStatus::AtRisk => ['Awaiting confirmation', 'warning'],
                    \App\Domain\Enums\OwnerStatus::DailyRetry => ['Urgent — confirm now', 'danger'],
                    \App\Domain\Enums\OwnerStatus::Disappeared => ['Marked as disappeared', 'dark'],
                };
            @endphp

            <h3 class="h5">
                Status:
                <span class="badge bg-{{ $statusLabel[1] }}">{{ $statusLabel[0] }}</span>
            </h3>

            @if ($owner->status === \App\Domain\Enums\OwnerStatus::Setup)
                <p class="text-muted mb-0">
                    Monitoring hasn't started yet. Finish the two steps below —
                    your 30-day check-in cycle begins as soon as both are done.
                </p>
            @else
                @if ($owner->status === \App\Domain\Enums\OwnerStatus::Disappeared)
                    <p class="text-muted">
                        We weren't able to confirm you were OK, so your information may
                        already have been shared with your emergency contacts. If
                        you're fine, click below to stop any further sharing and
                        resume normal check-ins.
                    </p>
                @elseif ($owner->next_checkin_due_at)
                    <p class="text-muted mb-0">
                        Next check-in due: {{ $owner->next_checkin_due_at->timezone($owner->timezone)->format('F j, Y H:i') }}
                        ({{ $owner->timezone }})
                    </p>
                @endif

                <form method="POST" action="{{ route('owner-checkin.store') }}" class="mt-3">
                    @csrf
                    <x-primary-button>
                        {{ __("I'm OK — check in now") }}
                    </x-primary-button>
                </form>
            @endif
        </div>
    </div>

    @if ($owner->status === \App\Domain\Enums\OwnerStatus::Setup)
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h3 class="h6">Finish setup</h3>
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
        <a href="{{ route('record.edit') }}" class="btn btn-outline-dark">Edit your record</a>
        <a href="{{ route('will-receivers.edit') }}" class="btn btn-outline-dark">Edit trusted contacts</a>
    </div>
</x-app-layout>
