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
