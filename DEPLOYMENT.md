# Deploying to production

One-time setup on a fresh DigitalOcean droplet, then deploys are
triggered by hand from GitHub Actions whenever you're ready to ship.

## 1. Create the droplet

- Ubuntu 24.04 LTS, the cheapest size that includes at least 2GB RAM
  (MySQL + several PHP containers on a 1GB box will swap/OOM).
- Add your SSH key during creation so you can log in immediately.
- No need to open any ports beyond 22 (SSH) in DigitalOcean's firewall —
  Cloudflare Tunnel makes only outbound connections, so 80/443 never need
  to be reachable from the internet.

## 2. Install Docker

```bash
curl -fsSL https://get.docker.com | sh
```

That installs the `docker compose` plugin too (verify with `docker compose version`).

## 3. Create a deploy user

Running everything as root works but isn't great practice. While
connected as root:

```bash
adduser deploy
usermod -aG docker deploy
```

`/home/deploy/.ssh/authorized_keys` needs two separate keys, for two
separate directions of access:

- **Your own key**, so you can `ssh deploy@<droplet-ip>` directly for
  manual work (steps 4, 5, 8 below, running `prod-artisan`, checking
  logs, ...). The key you added when creating the droplet in step 1
  works fine here too — add that same public key.
- **A dedicated key for GitHub Actions**, generated **on your own
  machine** (not the server):

  ```bash
  ssh-keygen -t ed25519 -f deploy_key -N ""
  ```

  Add `deploy_key.pub`'s contents too. Keep `deploy_key` (the private
  half) — it goes into a GitHub secret in step 6.

## 4. Clone the repo

As the `deploy` user:

```bash
git clone git@github.com:naneri/lastwillapp.git /home/deploy/lastwillapp
cd /home/deploy/lastwillapp
```

(Needs the droplet's own SSH key added as a GitHub deploy key, or clone
over HTTPS with a personal access token instead.)

## 5. Create the production `.env`

Copy `.env.example` to `.env` on the server — this file is never
committed, so it only ever exists on the droplet:

```bash
cp .env.example .env
```

Do this *before* trying to generate `APP_KEY` below — `docker compose`
needs `.env` to physically exist (`env_file: .env` fails outright on a
missing file), and `docker compose run` needs the `app` image to
exist, so build it first:

```bash
docker compose -f docker-compose.prod.yml build app
```

Now fill in real values in `.env`:

- `APP_ENV=production`, `APP_DEBUG=false`
- `APP_URL` — the real domain, e.g. `https://kanakeeper.com`
- `APP_KEY` — generate one: `docker compose -f docker-compose.prod.yml run --rm app php artisan key:generate --show`, paste the output in
- `DB_*` — pick real values for `DB_DATABASE`/`DB_USERNAME`/`DB_PASSWORD`; also set `DB_ROOT_PASSWORD` (read directly by `docker-compose.prod.yml` for the MySQL container, not by Laravel itself)
- `MAIL_*` — a real transactional provider (Postmark/Resend), not Mailpit
- `GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET` / `GOOGLE_REDIRECT_URI` — add `https://<your-domain>/auth/google/callback` as an authorized redirect URI in Google Cloud Console first (see the app's own notes on this)
- `PADDLE_*` — production (not sandbox) keys, and update the webhook URL in the Paddle dashboard to `https://<your-domain>/webhooks/paddle`
- `CLOUDFLARE_TUNNEL_TOKEN` — from step 7 below

## 6. Add GitHub repo secrets

Repo → Settings → Secrets and variables → Actions:

| Secret | Value |
|---|---|
| `SSH_HOST` | the droplet's IP address |
| `SSH_USERNAME` | `deploy` |
| `SSH_PRIVATE_KEY` | contents of `deploy_key` from step 3 |
| `DEPLOY_PATH` | `/home/deploy/lastwillapp` |

## 7. Set up the Cloudflare Tunnel

Needs your domain already on Cloudflare's nameservers (free).

1. Cloudflare dashboard → Zero Trust → **Networking → Tunnels** → **Create a tunnel**.
2. Choose **Cloudflared** as the connector, name it (e.g. `kanakeeper`).
3. On the "Install connector" step, pick **Docker** — it shows a `docker run` command containing a long token after `--token`. Copy just that token value into `.env` as `CLOUDFLARE_TUNNEL_TOKEN` (the `cloudflared` service in `docker-compose.prod.yml` already runs `tunnel run` with that token; you don't need to run the command it shows you).
4. Select the tunnel you just created → **Routes** tab → **Add route** → **Published application**. Domain = your domain, Service URL = `http://nginx:80` (the nginx container's name on the Docker network, reachable by service name — not `localhost`).

## 8. First deploy

```bash
chmod +x deploy.sh   # if the clone didn't preserve the executable bit
./deploy.sh
```

Watch for errors — this is the best time to catch a missing `.env`
value or a Docker build failure, before GitHub Actions is doing it
unattended.

## 9. Ongoing deploys

From here on, deploying means going to the repo's **Actions** tab →
**Deploy** → **Run workflow**. That triggers
[`.github/workflows/deploy.yml`](.github/workflows/deploy.yml), which
SSHes in and runs `deploy.sh`: pulls the latest commit on `master`,
rebuilds images, recreates containers, runs migrations, re-caches
config/routes/views, and restarts the queue workers. Pushing to
`master` by itself doesn't deploy anything — merge whenever, deploy
whenever you're ready.

## Running an artisan command by hand

SSH in and use [`prod-artisan`](prod-artisan), a thin wrapper around
`docker compose exec` against the running `app` container:

```bash
./prod-artisan tinker
./prod-artisan migrate:status
```

## Backups

MySQL data lives in the `mysql-data` Docker volume — nothing backs it up
automatically. At minimum, cron a nightly dump off the box:

```bash
docker compose -f docker-compose.prod.yml exec -T mysql \
  mysqldump -u root -p"$DB_ROOT_PASSWORD" "$DB_DATABASE" | gzip > backup-$(date +%F).sql.gz
```

Ship `backup-*.sql.gz` somewhere off the droplet (DigitalOcean Spaces, a
different server, etc.) — a backup that lives next to the thing it's
backing up doesn't protect against losing the droplet itself.
