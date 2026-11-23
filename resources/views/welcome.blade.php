<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

        {{-- Layout adapted from Start Bootstrap's "Landing Page" template
             (startbootstrap.com/theme/landing-page, MIT License) — masthead,
             feature grid, call-to-action and footer structure/CSS, with our
             own copy. Its stock showcase images and sample testimonials were
             dropped rather than carried over with placeholder content. --}}
        <style>
            .features-icons {
                padding-top: 7rem;
                padding-bottom: 7rem;
            }
            .features-icons .features-icons-item {
                max-width: 20rem;
            }
            .features-icons .features-icons-item .features-icons-icon {
                height: 7rem;
            }
            .features-icons .features-icons-item .features-icons-icon i {
                font-size: 4.5rem;
            }

            header.masthead {
                position: relative;
                background-color: #1b1b18;
                padding-top: 8rem;
                padding-bottom: 8rem;
            }
            @media (min-width: 768px) {
                header.masthead {
                    padding-top: 10rem;
                    padding-bottom: 10rem;
                }
            }

            .call-to-action {
                background-color: #1b1b18;
                padding-top: 5rem;
                padding-bottom: 5rem;
            }

            footer.footer {
                padding-top: 3rem;
                padding-bottom: 3rem;
            }
        </style>
    </head>
    <body>
        <nav class="navbar navbar-light bg-light static-top">
            <div class="container">
                <a class="navbar-brand" href="/"><x-app-name /></a>
                <div class="d-flex align-items-center gap-3">
                    <a class="text-decoration-none text-dark" href="{{ route('about') }}">About</a>
                    <a class="text-decoration-none text-dark" href="{{ route('pricing') }}">Pricing</a>
                    <a class="text-decoration-none text-dark" href="{{ route('faq') }}">FAQ</a>
                    @auth
                        <a class="btn btn-dark" href="{{ url('/dashboard') }}">Dashboard</a>
                    @else
                        <a class="btn btn-dark" href="{{ route('login') }}">Log in</a>
                    @endauth
                </div>
            </div>
        </nav>

        <header class="masthead text-white text-center">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-xl-7">
                        <h1 class="mb-4">A dead man's switch for the people who'd need to know</h1>
                        <p class="lead mb-4">
                            Write down where important documents live, who to contact, and
                            what to do next — once. Periodically confirm you're still around
                            with a simple check-in. If you ever stop, your trusted contacts
                            get what you left for them automatically, one at a time.
                        </p>
                        <div class="d-flex gap-3 justify-content-center">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="btn btn-light btn-lg">Dashboard</a>
                            @else
                                <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg">Log in</a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="btn btn-light btn-lg">Register</a>
                                @endif
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <section class="features-icons bg-light text-center">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="features-icons-item mx-auto mb-5 mb-lg-0 mb-lg-3">
                            <div class="features-icons-icon d-flex">
                                <i class="bi-journal-text m-auto text-dark"></i>
                            </div>
                            <h3>Write it once</h3>
                            <p class="lead mb-0">
                                Describe where things are and who to ask, in your own words
                                — not a legal will, just the information people would need.
                            </p>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="features-icons-item mx-auto mb-5 mb-lg-0 mb-lg-3">
                            <div class="features-icons-icon d-flex">
                                <i class="bi-calendar-check m-auto text-dark"></i>
                            </div>
                            <h3>Simple check-ins</h3>
                            <p class="lead mb-0">
                                Confirm you're OK every so often. Miss enough of them, and
                                that's when things start moving.
                            </p>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="features-icons-item mx-auto mb-0 mb-lg-3">
                            <div class="features-icons-icon d-flex">
                                <i class="bi-people-fill m-auto text-dark"></i>
                            </div>
                            <h3>Delivered to the right people</h3>
                            <p class="lead mb-0">
                                Your trusted contacts receive what you left for them
                                automatically, one at a time, in the order you choose.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        @unless (auth()->check())
            <section class="call-to-action text-white text-center">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-xl-6">
                            <h2 class="mb-4">Ready to get started?</h2>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="btn btn-light btn-lg">Register</a>
                            @endif
                        </div>
                    </div>
                </div>
            </section>
        @endunless

        <footer class="footer bg-light">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6 text-center text-lg-start">
                        <ul class="list-inline mb-2">
                            <li class="list-inline-item"><a href="{{ route('about') }}">About</a></li>
                            <li class="list-inline-item">⋅</li>
                            <li class="list-inline-item"><a href="{{ route('pricing') }}">Pricing</a></li>
                            <li class="list-inline-item">⋅</li>
                            <li class="list-inline-item"><a href="{{ route('faq') }}">FAQ</a></li>
                            <li class="list-inline-item">⋅</li>
                            <li class="list-inline-item"><a href="{{ route('terms') }}">Terms of Service</a></li>
                            <li class="list-inline-item">⋅</li>
                            <li class="list-inline-item"><a href="{{ route('privacy') }}">Privacy Policy</a></li>
                            <li class="list-inline-item">⋅</li>
                            <li class="list-inline-item"><a href="{{ route('refunds') }}">Refund Policy</a></li>
                        </ul>
                        <p class="text-muted small mb-0">&copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. All rights reserved.</p>
                    </div>
                </div>
            </div>
        </footer>
    </body>
</html>
