#!/usr/bin/env bash
#
# Ploi deploy script for pos.sherinhuts.com
#
# Paste the body of this file into:
#   Ploi -> Sites -> pos.sherinhuts.com -> Deploy script
#
# Ploi runs it as the site user (pos-4ugbp) and grants passwordless sudo
# for the php-fpm reload, so no sudo password is needed.
#
# Tracks the `production` branch, which points at b13749c8 - the code that
# ran from 2026-01-07 through 2026-08-27 and matches the live 23-table
# database.
#
# To move production to the POS V2 rewrite later, do NOT just change the
# branch here: v3 needs its own 101-table database. See deploy/deploy-v3.sh.

cd /home/pos-4ugbp/pos.sherinhuts.com || exit 1

# Take the site down, and guarantee it comes back up however this exits.
php artisan down --retry=60 || true
trap 'php artisan up || true' EXIT

git fetch origin production
git reset --hard origin/production

# Rebuild vendor/ for the deployed branch. No --ignore-platform-reqs: let a
# real PHP version mismatch fail loudly instead of being masked.
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# No npm step: this branch commits its compiled assets (public/css,
# public/js, public/themes, public/mix-manifest.json). Branch v3 does not -
# it needs `npm ci && npm run build`.

# No `php artisan migrate`: the live database already matches this branch.
# Its migrations table holds only 2 rows, so a migrate would attempt to
# recreate existing tables and fail partway through.

php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

sudo service php8.2-fpm reload

php artisan up

echo "🚀 Deployed $(git rev-parse --short HEAD) on $(git rev-parse --abbrev-ref HEAD)"
