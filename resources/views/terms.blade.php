<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Terms of Service — {{ config('app.name', 'Laravel') }}</title>

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

            <h1 class="h3 mb-1">Terms of Service</h1>
            <p class="text-muted small mb-4">Last updated: November 21, 2026</p>

            <p class="text-muted">
                These Terms of Service ("Terms") govern your use of
                {{ config('app.name', 'Laravel') }} (the "Service"), operated
                by us ("we", "us", "our"). By creating an account or using the
                Service, you agree to these Terms. If you don't agree, please
                don't use the Service.
            </p>

            <h2 class="h5 mt-4 mb-2">1. What the Service Is</h2>
            <p class="text-muted">
                {{ config('app.name', 'Laravel') }} lets you write a Record
                describing where to find important information, and
                designate Trusted Contacts who will receive that Record if
                you stop checking in over an extended period. You periodically
                confirm you're still active ("checking in"); if you miss
                enough check-ins in a row, the Service shares your Record
                with your designated contacts.
            </p>
            <p class="text-muted">
                <strong>This is not a legal will and has no legal effect on
                how your assets are distributed.</strong> It is not a
                substitute for a lawyer, an estate plan, or legally binding
                instructions. Use it as a note for the people who'd need to
                find things — not as your estate plan.
            </p>

            <h2 class="h5 mt-4 mb-2">2. Eligibility</h2>
            <p class="text-muted">
                You must be at least 18 years old and able to form a binding
                contract to use the Service. By using it, you confirm that's
                true of you.
            </p>

            <h2 class="h5 mt-4 mb-2">3. Your Account</h2>
            <p class="text-muted">
                You're responsible for keeping your login credentials secure
                and for all activity under your account. Use accurate
                information when you sign up — your Trusted Contacts will see
                your name and email address on the reveal email, so an
                unfamiliar one may be mistaken for spam. Tell us right away
                if you suspect unauthorized access to your account.
            </p>

            <h2 class="h5 mt-4 mb-2">4. Your Record and Trusted Contacts</h2>
            <p class="text-muted">
                You own the content of your Record. You grant us permission
                to store it (encrypted at rest), transmit it, and deliver it
                to the Trusted Contacts you designate, solely to operate the
                Service as described in these Terms.
            </p>
            <p class="text-muted">
                <strong>Don't store actual secrets in your Record</strong> —
                passwords, account numbers, PINs, and similar. Describe
                <em>where</em> to find things instead. Your Record is
                reachable through emailed links, and email accounts can be
                compromised; we're not liable for the consequences of secrets
                you chose to store directly.
            </p>
            <p class="text-muted">
                You're responsible for making sure the contact details you
                provide for your Trusted Contacts are accurate and that
                you've chosen people you actually trust — once your Record
                reaches their inbox, what they do with it is outside our
                control.
            </p>

            <h2 class="h5 mt-4 mb-2">5. Check-Ins and Reveals</h2>
            <p class="text-muted">
                The check-in and reveal process depends on things we can't
                fully guarantee — email deliverability, your contacts
                checking their inbox, and your own continued access to the
                email address on file. We don't guarantee exact timing, that
                a reveal email will be delivered or read, or that a missed
                check-in couldn't happen for reasons other than the one the
                Service is designed for (e.g. a lost email account). Confirming
                you're OK at any point, even after being marked unreachable,
                resets the process immediately.
            </p>

            <h2 class="h5 mt-4 mb-2">6. Subscriptions and Billing</h2>
            <p class="text-muted">
                New accounts get a free trial with monitoring and one Trusted
                Contact. Continued use after the trial requires a paid plan.
                Payments are processed by our payment provider, Paddle, who
                acts as the merchant of record for your purchase — their own
                terms apply to the transaction itself. Subscriptions renew
                automatically until cancelled; you can cancel any time
                through the billing portal in your dashboard, and cancellation
                takes effect at the end of the current billing period. See
                our <a href="{{ route('refunds') }}">Refund Policy</a> for
                details on refunds.
            </p>

            <h2 class="h5 mt-4 mb-2">7. Acceptable Use</h2>
            <p class="text-muted">
                Don't use the Service to store or transmit unlawful content,
                impersonate someone else, attempt to disrupt or probe the
                Service's security, or use it for anything other than its
                intended purpose. We may suspend or terminate accounts that
                violate this.
            </p>

            <h2 class="h5 mt-4 mb-2">8. Termination</h2>
            <p class="text-muted">
                You can ask us to close your account and delete your data at
                any time by contacting <a href="mailto:support@kanakeeper.com">support@kanakeeper.com</a>.
                We may suspend or terminate your access if you violate these
                Terms or if required by law.
            </p>

            <h2 class="h5 mt-4 mb-2">9. Disclaimers and Limitation of Liability</h2>
            <p class="text-muted">
                The Service is provided "as is," without warranties of any
                kind, express or implied. To the fullest extent permitted by
                law, we are not liable for indirect, incidental, or
                consequential damages, or for any failure of the check-in or
                reveal process to work as intended. Our total liability for
                any claim relating to the Service is limited to the amount
                you paid us in the 12 months before the claim arose.
            </p>

            <h2 class="h5 mt-4 mb-2">10. Changes to These Terms</h2>
            <p class="text-muted">
                We may update these Terms from time to time. If we make
                material changes, we'll post the updated Terms here and
                update the "last updated" date above; continuing to use the
                Service after that means you accept the changes.
            </p>

            <h2 class="h5 mt-4 mb-2">11. Governing Law</h2>
            <p class="text-muted">
                These Terms are governed by the laws of the Kyrgyz Republic,
                without regard to its conflict-of-law principles.
            </p>

            <h2 class="h5 mt-4 mb-2">12. Contact</h2>
            <p class="text-muted mb-0">
                Questions about these Terms? Email us at
                <a href="mailto:support@kanakeeper.com">support@kanakeeper.com</a>.
            </p>
        </div>
    </body>
</html>
