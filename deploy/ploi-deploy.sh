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

# --- PHP version -----------------------------------------------------------
# The site's FPM pool is /etc/php/8.2/fpm/pool.d/pos-4ugbp.conf, but the
# server's default CLI php is 8.3. Running composer under the CLI default
# generates vendor/composer/platform_check.php demanding PHP >= 8.3, which
# makes every request die with:
#
#     Composer detected issues in your platform:
#     Your Composer dependencies require a PHP version ">= 8.3.0".
#
# So pin every command to the same binary the site actually serves with.
# This is the correct fix; --ignore-platform-reqs only hides the mismatch.
PHP=/usr/bin/php8.2
COMPOSER="$(command -v composer)"

cd /home/pos-4ugbp/pos.sherinhuts.com || exit 1

# Take the site down, and guarantee it comes back up however this exits.
$PHP artisan down --retry=60 || true
trap '$PHP artisan up || true' EXIT

git fetch origin production
git reset --hard origin/production

$PHP "$COMPOSER" install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# No npm step: this branch commits its compiled assets (public/css,
# public/js, public/themes, public/mix-manifest.json). Branch v3 does not -
# it needs `npm ci && npm run build`.

# No `php artisan migrate`: the live database already matches this branch.
# Its migrations table holds only 2 rows, so a migrate would attempt to
# recreate existing tables and fail partway through.

$PHP artisan optimize:clear
$PHP artisan config:cache
$PHP artisan view:cache

# NO `artisan route:cache`. routes/admin.php assigns the name users.update
# twice - line 60 (update user) and line 226 (update roles, which should be
# roles.update). Route caching serialises names and refuses duplicates:
#
#     Unable to prepare route [admin/roles/edit/{role}] for serialization.
#     Another route has already been assigned name [users.update].
#
# The app runs fine without a route cache, just marginally slower. Re-enable
# this line once the duplicate name is fixed on the production branch.

sudo service php8.2-fpm reload

$PHP artisan up

echo "🚀 Deployed $(git rev-parse --short HEAD) on $(git rev-parse --abbrev-ref HEAD)"
