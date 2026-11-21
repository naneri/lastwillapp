<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Privacy Policy — {{ config('app.name', 'Laravel') }}</title>

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

            <h1 class="h3 mb-1">Privacy Policy</h1>
            <p class="text-muted small mb-4">Last updated: November 21, 2026</p>

            <p class="text-muted">
                This Privacy Policy explains what information
                {{ config('app.name', 'Laravel') }} ("we", "us", "our")
                collects, how we use it, and — because of what this Service
                does — specifically how and when it's shared with the
                Trusted Contacts you designate.
            </p>

            <h2 class="h5 mt-4 mb-2">1. Information We Collect</h2>
            <ul class="text-muted">
                <li><strong>Account information</strong> — your name, email address, and password (stored hashed, never in plain text).</li>
                <li><strong>Your Record</strong> — the content you write describing where to find things, encrypted at rest.</li>
                <li><strong>Trusted Contact details</strong> — the names and email addresses of the people you designate to receive your Record.</li>
                <li><strong>Billing information</strong> — handled directly by our payment processor, Paddle; we don't receive or store your full card details.</li>
                <li><strong>Google account info</strong> — if you sign in with Google, we receive your name and email address from Google, not your Google password.</li>
                <li><strong>Usage and log data</strong> — check-in timestamps, and standard web server logs (IP address, browser type, timestamps) for security and troubleshooting.</li>
                <li><strong>Cookies</strong> — a session cookie to keep you signed in. We don't use advertising or tracking cookies.</li>
            </ul>

            <h2 class="h5 mt-4 mb-2">2. How We Use It</h2>
            <p class="text-muted">
                We use this information to operate the check-in and reveal
                process, send you check-in reminders and account emails,
                process billing, provide customer support, and keep the
                Service secure. We don't sell your information or use it for
                advertising.
            </p>

            <h2 class="h5 mt-4 mb-2">3. How Your Record Is Shared</h2>
            <p class="text-muted">
                This is the core thing the Service does, so we want to be
                explicit about it: <strong>if you stop checking in for an
                extended period (roughly 15–45 days, depending on your cycle
                when you stop responding), we will email your Record and any
                message you wrote to the Trusted Contact(s) you
                designated.</strong> Outside of that triggered reveal, we
                don't share your Record with anyone. What a Trusted Contact
                does with your Record after they receive it is outside our
                control.
            </p>

            <h2 class="h5 mt-4 mb-2">4. Third-Party Service Providers</h2>
            <p class="text-muted">
                We rely on a small number of providers to run the Service,
                each of which processes data only as needed to provide their
                part of it:
            </p>
            <ul class="text-muted">
                <li><strong>Paddle</strong> — billing and payment processing.</li>
                <li><strong>Resend</strong> — transactional email delivery (check-in reminders, reveal emails).</li>
                <li><strong>Cloudflare</strong> — DNS and support-inbox email routing.</li>
                <li><strong>DigitalOcean</strong> — application hosting and our database.</li>
                <li><strong>Google</strong> — optional sign-in (Google OAuth).</li>
            </ul>

            <h2 class="h5 mt-4 mb-2">5. Data Security</h2>
            <p class="text-muted">
                Your Record is encrypted at rest, passwords are hashed, and
                the Service is served over HTTPS. No method of storage or
                transmission is 100% secure, but we take reasonable steps to
                protect your information.
            </p>

            <h2 class="h5 mt-4 mb-2">6. Data Retention and Deletion</h2>
            <p class="text-muted">
                We keep your information while your account is active. To
                close your account and have your data deleted, email
                <a href="mailto:support@kanakeeper.com">support@kanakeeper.com</a>
                — we'll process the request promptly, aside from records we're
                required to keep for billing, tax, or legal reasons.
            </p>

            <h2 class="h5 mt-4 mb-2">7. Your Rights and Choices</h2>
            <p class="text-muted">
                Depending on where you live, you may have rights to access,
                correct, or delete your personal information, or to object to
                certain processing (for example under the GDPR or CCPA).
                You can exercise any of these by emailing
                <a href="mailto:support@kanakeeper.com">support@kanakeeper.com</a>.
            </p>

            <h2 class="h5 mt-4 mb-2">8. Children's Privacy</h2>
            <p class="text-muted">
                The Service isn't directed at, and isn't intended for use by,
                anyone under 18.
            </p>

            <h2 class="h5 mt-4 mb-2">9. Changes to This Policy</h2>
            <p class="text-muted">
                We may update this policy from time to time. If we make
                material changes, we'll post the updated policy here and
                update the "last updated" date above.
            </p>

            <h2 class="h5 mt-4 mb-2">10. Contact</h2>
            <p class="text-muted mb-0">
                Questions about this policy or your data? Email us at
                <a href="mailto:support@kanakeeper.com">support@kanakeeper.com</a>.
            </p>
        </div>
    </body>
</html>
