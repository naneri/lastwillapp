#!/usr/bin/env bash
# Runs ON the production server — triggered over SSH by
# .github/workflows/deploy.yml on every push to master. Not meant to run
# locally; the dev environment uses docker-compose.yml instead.
set -euo pipefail

cd "$(dirname "$0")"

echo "==> Pulling latest code"
git fetch origin master
git reset --hard origin/master

echo "==> Building images"
docker compose -f docker-compose.prod.yml build

echo "==> Waiting for MySQL"
docker compose -f docker-compose.prod.yml up -d --wait mysql

# Must happen before the rest of the stack starts, not after — queue and
# scheduler query app tables (cache, jobs, users, ...) as soon as they
# boot, so on a fresh install they'd crash-loop against tables that don't
# exist yet if migrations ran after `up -d` instead of before it.
echo "==> Running migrations"
docker compose -f docker-compose.prod.yml run --rm app php artisan migrate --force

echo "==> Recreating containers"
docker compose -f docker-compose.prod.yml up -d

echo "==> Caching config/routes/views"
docker compose -f docker-compose.prod.yml exec -T app php artisan config:cache
docker compose -f docker-compose.prod.yml exec -T app php artisan route:cache
docker compose -f docker-compose.prod.yml exec -T app php artisan view:cache

echo "==> Restarting queue workers so they pick up the new code"
docker compose -f docker-compose.prod.yml exec -T app php artisan queue:restart

echo "==> Removing unused images"
docker image prune -f

echo "==> Deploy complete"
