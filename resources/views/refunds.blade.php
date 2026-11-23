<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Refund Policy — {{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container py-5" style="max-width: 40rem;">
            <p class="mb-4">
                <a href="/" class="text-decoration-none text-muted small">&larr; Back to home</a>
            </p>

            <h1 class="h3 mb-1">Refund Policy</h1>
            <p class="text-muted small mb-4">Last updated: November 23, 2026</p>

            <p class="text-muted">
                Every account starts with a free, 100-day trial — full
                monitoring and one trusted contact, no card required — so
                you can try {{ config('app.name', 'Laravel') }} out before
                paying for anything. This policy covers what happens once
                you do subscribe.
            </p>

            <h2 class="h5 mt-4 mb-2">1. First subscription: 14-day money-back guarantee</h2>
            <p class="text-muted">
                If you subscribe to a paid plan and decide it's not for you,
                email <a href="mailto:support@kanakeeper.com">support@kanakeeper.com</a>
                within 14 days of that first charge and we'll refund it in
                full, no questions asked.
            </p>

            <h2 class="h5 mt-4 mb-2">2. Renewals</h2>
            <p class="text-muted">
                Subscriptions renew automatically each year until
                cancelled. Renewal charges aren't covered by the 14-day
                guarantee above, but we'll always refund a renewal that was
                charged in error — for example a duplicate charge, or one
                that went through after you'd already cancelled.
            </p>

            <h2 class="h5 mt-4 mb-2">3. Cancelling</h2>
            <p class="text-muted">
                You can cancel any time through the billing portal in your
                dashboard. Cancelling stops future renewals, but you keep
                your plan's access through the end of the period you've
                already paid for — we don't prorate or refund the unused
                remainder of a billing period.
            </p>

            <h2 class="h5 mt-4 mb-2">4. Upgrades</h2>
            <p class="text-muted">
                Upgrading to a higher plan is billed immediately, prorated
                for whatever time was left on your previous plan — you're
                credited for that automatically at checkout, so there's
                nothing separate to refund.
            </p>

            <h2 class="h5 mt-4 mb-2">5. How to request a refund</h2>
            <p class="text-muted">
                Email <a href="mailto:support@kanakeeper.com">support@kanakeeper.com</a>
                with the address on your account and the reason for the
                request. You can also request one directly through Paddle,
                our payment provider and the merchant of record for your
                purchase, using the link in your order receipt email.
                Approved refunds are returned to your original payment
                method by Paddle, typically within 5–10 business days.
            </p>

            <h2 class="h5 mt-4 mb-2">6. Contact</h2>
            <p class="text-muted mb-0">
                Questions about this policy? Email us at
                <a href="mailto:support@kanakeeper.com">support@kanakeeper.com</a>.
            </p>
        </div>
    </body>
</html>
