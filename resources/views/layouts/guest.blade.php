<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.google-tag')

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0/dist/css/adminlte.css" rel="stylesheet">
    </head>
    <body class="login-page bg-body-secondary">
        <div class="login-box">
            <div class="text-center mb-2">
                <a href="/" class="text-decoration-none text-muted small">
                    &larr; {{ __('Back to home') }}
                </a>
            </div>

            <div class="login-logo">
                <a href="/"><x-app-name /></a>
            </div>

            <div class="card">
                <div class="card-body login-card-body">
                    {{ $slot }}
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    </body>
</html>
