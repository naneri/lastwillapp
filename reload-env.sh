#!/usr/bin/env bash
# Runs ON the production server, by hand, after editing .env — no code/image
# change involved, so this skips deploy.sh's git pull, image build, and
# migrations and just gets the new .env values live.
set -euo pipefail

cd "$(dirname "$0")"

echo "==> Recreating containers so the updated .env is read"
docker compose -f docker-compose.prod.yml up -d

echo "==> Caching config/routes/views"
docker compose -f docker-compose.prod.yml exec -T app php artisan config:cache
docker compose -f docker-compose.prod.yml exec -T app php artisan route:cache
docker compose -f docker-compose.prod.yml exec -T app php artisan view:cache

echo "==> Restarting queue workers so they pick up the new config"
docker compose -f docker-compose.prod.yml exec -T app php artisan queue:restart

echo "==> Env reload complete"
