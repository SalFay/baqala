#!/usr/bin/env bash
#
# Deploy branch v3 (POS V2 rewrite, Laravel 11 + Vite + Inertia).
#
# ---------------------------------------------------------------------------
# READ THIS BEFORE RUNNING
#
# v3 needs a 101-table schema. The database currently attached to
# pos.sherinhuts.com has 23 tables and belongs to the OLD system. Pointing v3
# at it is what took the site down on 2026-08-27 with:
#
#     SQLSTATE[42S02]: Base table or view not found:
#     1146 Table 'sherinhuts_pos.settings' doesn't exist
#
# You cannot migrate the old database up to v3. Its migrations table holds
# only 2 rows, both from the old system, so all 51 of v3's migrations are
# unregistered and `php artisan migrate` would try to recreate live tables.
#
# v3 therefore needs its OWN database. Set DB_DATABASE in .env to a new,
# empty database before running this. Production will start empty; moving
# products, customers and sales across is a separate data-migration job.
# ---------------------------------------------------------------------------
#
# Run as the site user:
#     ssh baqala-ploi
#     sudo -u pos-4ugbp -i bash
#     bash /home/pos-4ugbp/pos.sherinhuts.com/deploy/deploy-v3.sh
#
set -euo pipefail

SITE="/home/pos-4ugbp/pos.sherinhuts.com"
STAMP="$(date +%F-%H%M)"

cd "$SITE"

echo "==> Preflight"
php -v | head -1
whoami
test -w . || { echo "FATAL: no write access here. Run as pos-4ugbp."; exit 1; }
command -v npm >/dev/null || { echo "FATAL: npm not found. v3 needs a Vite build."; exit 1; }

DB_NAME="$(grep -E '^DB_DATABASE=' .env | cut -d= -f2- | tr -d '"'"'"'')"
echo "    target database: ${DB_NAME}"
echo
read -r -p "Is '${DB_NAME}' a NEW, EMPTY database for v3? [yes/NO] " ok
[ "$ok" = "yes" ] || { echo "Aborted. Point DB_DATABASE at a fresh database first."; exit 1; }

echo
echo "==> Backups"
cp -a .env "$HOME/env-backup-$STAMP"
if [ -d public/assets ]; then
  cp -a public/assets "$HOME/assets-backup-$STAMP"
fi
echo "    saved to $HOME/*-backup-$STAMP"

echo
echo "==> Maintenance mode"
php artisan down --retry=60 || true

cleanup() { php artisan up || true; }
trap cleanup EXIT

echo
echo "==> Fetching v3"
git -c safe.directory="$SITE" fetch origin v3
git -c safe.directory="$SITE" reset --hard origin/v3

echo
echo "==> PHP dependencies"
# No --ignore-platform-reqs: composer.json requires php ^8.2 and the server
# runs 8.2+. Let a genuine mismatch fail loudly rather than be masked.
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

echo
echo "==> Frontend build"
# Required: public/build is gitignored, so no compiled assets ship with v3.
# Without this the app renders a blank page.
npm ci
npm run build

echo
echo "==> Database"
php artisan migrate --force
php artisan db:seed --force

echo
echo "==> Storage link + caches"
php artisan storage:link || true
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo
echo "==> Deployed $(git -c safe.directory="$SITE" rev-parse --short HEAD) on $(git -c safe.directory="$SITE" rev-parse --abbrev-ref HEAD)"
echo
echo "Next, exit to the ploi user and run:"
echo "  sudo service php8.2-fpm reload"
echo "  curl -s -o /dev/null -w '%{http_code}\\n' https://pos.sherinhuts.com/login"
