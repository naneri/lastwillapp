<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="d-flex flex-column justify-content-center align-items-center min-vh-100 py-4">
            <div class="mb-2">
                <a href="/" class="text-decoration-none text-muted small">
                    &larr; {{ __('Back to home') }}
                </a>
            </div>

            <div class="mb-4">
                <a href="/" class="text-decoration-none text-dark fs-4 fw-semibold">
                    {{ config('app.name', 'Laravel') }}
                </a>
            </div>

            <div class="card shadow-sm" style="width: 100%; max-width: 28rem;">
                <div class="card-body p-4">
                    {{ $slot }}
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
