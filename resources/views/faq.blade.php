<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>FAQ — {{ config('app.name', 'Laravel') }}</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container py-5" style="max-width: 40rem;">
            <p class="mb-4">
                <a href="/" class="text-decoration-none text-muted small">&larr; Back to home</a>
            </p>

            <h1 class="h3 mb-4">Frequently Asked Questions</h1>

            <h2 class="h5 mb-3">Trust &amp; legal</h2>

            <div class="mb-4">
                <p class="fw-bold mb-1">Is this a legal will?</p>
                <p class="text-muted">
                    No. It has no legal effect on how your assets are
                    distributed — think of it as a note for the people who'd
                    need to find things, not a replacement for a lawyer or
                    estate plan.
                </p>
            </div>

            <div class="mb-4">
                <p class="fw-bold mb-1">What should I actually write in my Record?</p>
                <p class="text-muted">
                    Where things are and who to ask, not the secrets
                    themselves (e.g. "the password list is in my desk
                    drawer," not the password). It's reachable by email, and
                    email accounts can be compromised.
                </p>
            </div>

            <div class="mb-4">
                <p class="fw-bold mb-1">Is my information encrypted?</p>
                <p class="text-muted">
                    Yes, your Record is encrypted at rest.
                </p>
            </div>

            <div class="mb-4">
                <p class="fw-bold mb-1">Can my trusted contact share what they receive with others?</p>
                <p class="text-muted mb-0">
                    That's outside our control once it's in their inbox —
                    choose people you actually trust.
                </p>
            </div>

            <h2 class="h5 mb-3">How the check-in process works</h2>

            <div class="mb-4">
                <p class="fw-bold mb-1">How often do I need to check in?</p>
                <p class="text-muted">
                    Roughly once a month, via a one-click link in an email.
                </p>
            </div>

            <div class="mb-4">
                <p class="fw-bold mb-1">What happens if I miss one?</p>
                <p class="text-muted">
                    You're not marked unreachable immediately — you get a
                    follow-up reminder with a few more days, then daily
                    reminders for about a week. Only after all of those go
                    unanswered does anything get shared.
                </p>
            </div>

            <div class="mb-4">
                <p class="fw-bold mb-1">I was on vacation and missed everything — now what?</p>
                <p class="text-muted">
                    Confirming you're OK at any point, even after being
                    marked unreachable, immediately resets everything.
                    Nothing is undone or re-sent incorrectly.
                </p>
            </div>

            <div class="mb-4">
                <p class="fw-bold mb-1">How soon will my trusted contacts receive my information if I stop checking in?</p>
                <p class="text-muted">
                    Approximately between 15 and 45 days, depending on
                    when in your check-in cycle you stop responding.
                </p>
            </div>

            <div class="mb-4">
                <p class="fw-bold mb-1">What does my trusted contact actually receive?</p>
                <p class="text-muted">
                    One email with your Record and a link asking them to
                    confirm receipt. They hear nothing from us before that
                    point.
                </p>
            </div>

            <div class="mb-4">
                <p class="fw-bold mb-1">Do I have to use my real name on my account?</p>
                <p class="text-muted">
                    No, but it's recommended. The email your trusted contact
                    receives shows the name and email address on your
                    account — if neither looks familiar, they may mistake it
                    for spam and ignore it.
                </p>
            </div>

            <div class="mb-4">
                <p class="fw-bold mb-1">What if my first contact doesn't check their email?</p>
                <p class="text-muted">
                    If they don't confirm in time, it moves to your
                    next-ranked contact (plans with more than one) — so it
                    doesn't depend on one person seeing one email.
                </p>
            </div>

            <div class="mb-4">
                <p class="fw-bold mb-1">Can I change my trusted contacts later?</p>
                <p class="text-muted mb-0">
                    Yes, any time.
                </p>
            </div>

            <h2 class="h5 mb-3">Account &amp; billing</h2>

            <div class="mb-4">
                <p class="fw-bold mb-1">Is there a free plan?</p>
                <p class="text-muted">
                    Every new account gets a 100-day free trial with
                    monitoring and 1 trusted contact. After that, a paid
                    plan keeps it running.
                </p>
            </div>

            <div class="mb-0">
                <p class="fw-bold mb-1">Can I cancel anytime?</p>
                <p class="text-muted mb-0">
                    Yes, through the billing portal.
                </p>
            </div>
        </div>
    </body>
</html>
