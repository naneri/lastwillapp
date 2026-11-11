<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name') }}</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container py-5" style="max-width: 640px;">
            <h1 class="h4">What {{ $owner->name }} left for you</h1>

            <p class="text-muted">
                {{ $owner->name }} ({{ $owner->email }}) set this up so you'd
                have this information if they ever stopped checking in.
            </p>

            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    @if ($record && filled($record->body))
                        <p style="white-space: pre-wrap;">{{ $record->body }}</p>
                    @else
                        <p class="text-muted mb-0">No record was written.</p>
                    @endif
                </div>
            </div>

            @if ($reveal->willReceiver->message)
                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <h2 class="h6">A message for you</h2>
                        <p class="mb-0" style="white-space: pre-wrap;">{{ $reveal->willReceiver->message }}</p>
                    </div>
                </div>
            @endif

            @if ($reveal->isConfirmed())
                <div class="alert alert-success">
                    You marked this as received on {{ $reveal->confirmed_at->format('F j, Y H:i') }}.
                </div>
            @else
                <form method="POST" action="{{ route('reveal.confirm', $reveal->token) }}">
                    @csrf
                    <button type="submit" class="btn btn-dark">Mark as received</button>
                </form>
            @endif
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
