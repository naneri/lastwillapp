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
            <p class="text-muted mb-4" style="max-width: 32rem;">
                Record where your important information lives, name two people you
                trust, and we'll make sure they can reach it if you ever stop
                checking in.
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
