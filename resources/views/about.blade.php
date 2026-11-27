<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.google-tag')

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>About — {{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container py-5" style="max-width: 40rem;">
            <p class="mb-4">
                <a href="/" class="text-decoration-none text-muted small">&larr; Back to home</a>
            </p>

            <h1 class="h3 mb-4">About {{ config('app.name', 'Laravel') }}</h1>

            <h2 class="h5 mb-3">Why I built this</h2>

            <p class="text-muted mb-3">
                There's a story that's stuck with me: a man passed away, and
                not long after, an acquaintance of his brought the family a
                diary — pages of memories from his time during the Second
                World War, and his own thoughts on the personal and global
                events that shaped his life. He'd kept it from his children
                while he was alive; it held a lot of sad moments he didn't
                want them carrying while he was still around to see.
                Instead, he trusted someone to make sure it reached them
                once he no longer could.
            </p>
            <p class="text-muted mb-3">
                What stays with me isn't the diary itself so much as the
                arrangement behind it: wanting the people you love to have
                something, but only once you're no longer there to hand it
                over yourself. Most of us never set anything like that up,
                and when we do think about it, it usually comes down to
                trusting one specific person to remember, and to actually
                follow through, whenever that day comes.
            </p>
            <p class="text-muted mb-4">
                So I built {{ config('app.name', 'Laravel') }} to do that
                automatically — a simple place to write down what matters,
                once, and a system that makes sure it reaches the people you
                trust if you ever go silent, without depending on any one
                person remembering to pass it along. Not a diary, and not a
                legal will — just a safety net for the things only you know.
            </p>

            <h2 class="h5 mb-3">Who's behind it</h2>

            <p class="text-muted mb-4">
                I'm Kanat Tabaldiev, building and running this myself as a
                Software Developer based in the Kyrgyz Republic.
            </p>

            <p class="text-muted mb-4">
                {{ config('app.name', 'Laravel') }} is a "dead man's switch"
                style service: you record the information your family or
                trusted contacts would need if something happened to you
                <span class="small opacity-75">(for example, where your
                will or insurance documents are kept, who your lawyer or
                accountant is, where to find a personal diary, instructions
                for pets or dependents, etc...)</span>, and
                periodically confirm you're still around with a simple
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
                    <strong>Periodically confirm you're OK with a single
                    click.</strong> A simple check-in email arrives roughly
                    every month.
                </li>
                <li class="mb-3">
                    <strong>Miss one, and reminders escalate.</strong>
                    You'll get increasingly urgent follow-ups over the
                    following 2 weeks — plenty of chances to confirm before
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
