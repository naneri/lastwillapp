# LastWillApp

A "dead man's switch" style service: you record where your important
information lives (accounts, assets, instructions — anything you'd want
close people to find if something happened to you), name two trusted
contacts, and the app periodically checks that you're still reachable.
Miss enough check-ins and it automatically emails your trusted contacts
so they can retrieve what you left behind.

See [SPEC.md](SPEC.md) for the full product spec (actors, data model,
check-in/escalation state machine, functional requirements) and
[STACK.md](STACK.md) for the technical stack and local dev setup.

## Stack

- Laravel (Blade + Bootstrap, no Livewire/SPA — see STACK.md for why)
- MySQL
- Laravel's scheduler + queue for the check-in/escalation mechanic
- Mailpit for local email inspection

## Local development

Requires Docker.

```bash
docker compose up -d
```

This starts:

- `app` — PHP-FPM running the Laravel app
- `nginx` — serves the app at [http://localhost:8080](http://localhost:8080)
- `mysql` — the database
- `mailpit` — catches all outgoing mail; inbox at [http://localhost:8025](http://localhost:8025)
- `scheduler` — runs `php artisan schedule:work`, driving the check-in/escalation state machine
- `queue` — runs `php artisan queue:work`, sending queued mail

First-time setup (migrations):

```bash
docker compose exec app php artisan migrate
```

### After pulling new code

`queue` and `scheduler` are long-running processes that boot the app once
and keep it in memory — they won't pick up code changes until restarted:

```bash
docker compose restart queue scheduler
```

Restarting `app` on its own (e.g. after an `.env` change) also needs `nginx`
restarted right after — nginx resolves the `app` container's IP once at
startup and doesn't notice it change, so requests 502 until nginx is
restarted too:

```bash
docker compose restart app nginx
```

## Billing (Paddle)

Plans and pricing are described in [SPEC.md §12](SPEC.md). The billing
page and webhook work fine locally with no configuration — they just
no-op (upgrade buttons stay disabled, the webhook rejects everything since
there's no secret to check signatures against). To actually go live:

1. Create a Paddle account and, in **Developer tools → Authentication**,
   grab a client-side token (its own tab) and a server-side API key. The
   API key needs `customer_portal_session.write` (for the "Manage or
   cancel subscription" link) and `subscription.write` (for upgrades —
   `BillingController::upgrade()` changes an existing subscription's price
   directly rather than opening a second checkout).
2. Create two products/prices (Basic, Pro) in the Paddle dashboard and
   copy their price IDs.
3. Set a **default payment link** under **Checkout → Checkout
   configuration** (e.g. `https://<your-domain>/billing`) — Paddle
   rejects every checkout until this is set, even just to test the
   overlay. The domain also needs approving under **Website approval**,
   sandbox included, despite Paddle's docs saying sandbox skips this.
4. Create a webhook pointed at `https://<your-domain>/webhooks/paddle`
   subscribed to `subscription.created`, `subscription.activated`,
   `subscription.updated`, and `subscription.canceled`; copy its secret.
5. Set `PADDLE_SANDBOX`, `PADDLE_CLIENT_TOKEN`, `PADDLE_API_KEY`,
   `PADDLE_WEBHOOK_SECRET`, `PADDLE_PRICE_BASIC`, and `PADDLE_PRICE_PRO`
   in `.env` (see `.env.example`).

For local testing before a real domain exists, a tunnel (e.g.
`cloudflared tunnel --url http://localhost:8080`) stands in for
`<your-domain>` in steps 3–4.

This isn't integrated via `laravel/cashier-paddle` — its latest release
still requires Guzzle `^7.4.5`, which conflicts with the Guzzle 8 that
Laravel 13 already pulls in here. Instead, `app/Http/Controllers/BillingController.php`
and `PaddleWebhookController.php` talk to Paddle directly (Paddle.js for
checkout, Paddle's REST API + manually-verified webhook signatures for
everything server-side).
