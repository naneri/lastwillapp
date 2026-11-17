<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Terms of Service — {{ config('app.name', 'Laravel') }}</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container py-5" style="max-width: 40rem;">
            <p class="mb-4">
                <a href="/" class="text-decoration-none text-muted small">&larr; Back to home</a>
            </p>

            <h1 class="h3 mb-4">Terms of Service</h1>

            <div class="alert alert-warning">
                This is a placeholder. {{ config('app.name', 'Laravel') }} doesn't have
                finalized Terms of Service yet — this page exists so the registration
                form has somewhere real to link to in the meantime.
            </div>

            <p class="text-muted">
                Once finalized, the terms governing use of
                {{ config('app.name', 'Laravel') }} — including how your data is
                stored, how the check-in and reveal process works, and your
                rights and responsibilities as an account owner — will be
                published here.
            </p>
        </div>
    </body>
</html>
