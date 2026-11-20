# Kana Legacy Keeper — Tech Stack (v0.1)

Decided after reviewing [SPEC.md](SPEC.md). Chosen to fit the owner's
existing PHP/Laravel experience, a self-hosted VPS, and a small
($10-30/mo) budget — and to lean on Laravel's built-in primitives for the
spec's trickiest parts (time-based state machine, expiring/single-use
links, mail).

## Backend
- **Laravel** (current LTS/stable release).

## Frontend
- **Blade templates + Bootstrap** (CDN or simple compiled asset).
- Plain forms and full-page requests — no Livewire, no SPA, no
  client-side framework. Nothing in this app needs real-time/reactive UI;
  check-ins, record editing, and receiver pages are all simple
  submit-and-reload flows.

## Database
- **MySQL** — simplest to run/back up on a small VPS, default in the
  Laravel ecosystem.

## Scheduling (core mechanic for §4/§5 of the spec)
- A single cron entry on the VPS: `* * * * * php artisan schedule:run`.
- Laravel scheduled commands (e.g. `app:process-checkins`,
  `app:process-escalations`) run daily, scanning *all* owners and sending
  whatever check-in/escalation emails are due based on their current
  state and timestamps. One evaluated-on-a-timer source of truth, not
  scattered per-user jobs.
- Laravel's `Carbon::setTestNow` time-travel helpers make this state
  machine testable without waiting on real clocks — important given how
  central correct timing is to the whole product.

## Check-in & reveal links
- **DB-backed single-use/expiring tokens**, not bare Laravel signed URLs.
  Needed because:
  - The spec requires an audit trail (every check-in/reveal event
    logged) — a token row with status/timestamps gives this naturally.
  - Willreceiver #1's reveal link must stay valid *indefinitely* even
    after escalating to #2 (§10.1) — a per-link DB status handles that;
    a signed URL alone doesn't carry revocable/stateful per-link info.

## Email
- Laravel Mail, backed by a dedicated transactional provider — **Postmark
  or Resend** (not raw SMTP from the VPS). Deliverability is this app's
  single point of failure (§8); worth the small monthly cost at this
  scale.

## Auth
- **Laravel Breeze or Fortify** (Blade variant, not the Livewire/Inertia
  variants). Fortify specifically leaves room to add 2FA later without a
  rewrite (matches §10.2 — 2FA planned, not required, for v1).

## Hosting
- **A bare DigitalOcean VPS running docker-compose.prod.yml** — the same
  Docker Compose shape as local dev (app/nginx/mysql/queue/scheduler),
  just built as standalone images instead of bind-mounted, plus a
  `cloudflared` container. No Forge; deploys are a GitHub Actions
  workflow that SSHes in and runs `deploy.sh`. See
  [DEPLOYMENT.md](DEPLOYMENT.md) for the full setup and deploy process.
- **Cloudflare Tunnel** for HTTPS/ingress instead of nginx+Let's
  Encrypt — the droplet opens no ports to the internet beyond SSH;
  `cloudflared` makes an outbound-only connection and Cloudflare
  terminates TLS at the edge. Same approach already used for local dev
  tunneling, free for this use case.

## Local development
- **Docker Compose** for local dev, mirroring production services:
  - `app` — PHP-FPM + Laravel
  - `nginx` — serves the app
  - `mysql` — local database
  - `mailpit` (or Mailhog) — catches all outgoing mail locally instead of
    sending real emails, with a web UI to inspect them. Given how much of
    this app's behavior is email-driven (check-ins, escalations, reveal
    links), being able to see every email the scheduler sends during dev
    without real inboxes is important, not just a nice-to-have.
- Production still uses the real transactional provider (Postmark/Resend)
  — only local/dev `.env` points mail at Mailpit.

## Explicitly not used (for now)
- Livewire, Alpine, any JS framework/SPA.
- Tailwind (using Bootstrap instead).
- Redis/queue-driver complexity beyond Laravel's database queue driver,
  unless scale later demands it.
