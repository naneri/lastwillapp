<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>About — {{ config('app.name', 'Laravel') }}</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container py-5" style="max-width: 40rem;">
            <p class="mb-4">
                <a href="/" class="text-decoration-none text-muted small">&larr; Back to home</a>
            </p>

            <h1 class="h3 mb-4">About {{ config('app.name', 'Laravel') }}</h1>

            <div class="alert alert-warning">
                This is a placeholder. The full story behind
                {{ config('app.name', 'Laravel') }} — why it exists and
                who's behind it — will go here. The "How it works" section
                below is accurate, not a placeholder.
            </div>

            <p class="text-muted mb-4">
                {{ config('app.name', 'Laravel') }} is a "dead man's switch"
                style service: you record the information your family or
                trusted contacts would need if something happened to you,
                and periodically confirm you're still around with a simple
                check-in. If you ever stop, that information is delivered
                automatically to the people you've named.
            </p>

            <h2 class="h5 mb-3">How it works</h2>

            <ol class="mb-0">
                <li class="mb-3">
                    <strong>Write your Record.</strong> Describe where
                    things are, who to contact, and what to do — once, in
                    your own words.
                </li>
                <li class="mb-3">
                    <strong>Name your Trusted Contacts.</strong> One or more
                    people who'd receive your Record. How many depends on
                    your plan.
                </li>
                <li class="mb-3">
                    <strong>Confirm you're OK, periodically.</strong> A
                    simple check-in email, roughly every month.
                </li>
                <li class="mb-3">
                    <strong>Miss one, and reminders escalate.</strong>
                    You'll get increasingly urgent follow-ups over the
                    following weeks — plenty of chances to confirm before
                    anything happens.
                </li>
                <li class="mb-3">
                    <strong>Still no response? Your first contact is
                    notified.</strong> They receive your Record by email and
                    have to confirm they got it.
                </li>
                <li class="p-2 bg-white border rounded">
                    <span class="text-muted small d-block">(For paid accounts only)</span>
                    <strong>If they don't confirm in time, it moves to the
                    next contact.</strong> So it doesn't depend on just one
                    person checking one email.
                </li>
            </ol>

            <p class="mt-4 mb-0 text-center">
                Still confused? Read our <a href="{{ route('faq') }}">FAQ</a>.
            </p>
        </div>
    </body>
</html>
