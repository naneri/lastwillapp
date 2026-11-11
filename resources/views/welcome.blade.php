<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="d-flex flex-column justify-content-center align-items-center min-vh-100 text-center px-3">
            <h1 class="mb-3">{{ config('app.name', 'Laravel') }}</h1>
            <p class="text-muted mb-4" style="max-width: 36rem;">
                A dead man's switch for the information your family would need if
                something happened to you — where important documents live, who to
                contact, instructions for what to do next. Write it down once, and
                periodically confirm you're still around with a simple check-in.
            </p>
            <p class="text-muted mb-4" style="max-width: 36rem;">
                If you ever stop checking in, we won't assume the worst right away
                — you'll get reminders first. But if those go unanswered too, the
                trusted contacts you've named will automatically receive the
                information you left for them, one at a time, so nothing important
                depends on someone finding the right folder or asking the right
                question at the wrong time.
            </p>

            <div class="d-flex gap-3">
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn btn-dark">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-dark">Log in</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn btn-dark">Register</a>
                    @endif
                @endauth
            </div>
        </div>
    </body>
</html>
