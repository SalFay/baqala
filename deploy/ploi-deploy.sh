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
# Two separate things are going on here. Both matter.
#
# 1. The site's FPM pool is /etc/php/8.2/fpm/pool.d/pos-4ugbp.conf, but the
#    server's default CLI php is 8.3. Composer writes
#    vendor/composer/platform_check.php against whichever PHP it runs under,
#    so running it as the default `composer` builds vendor/ for 8.3 and every
#    request then dies with:
#
#        Composer detected issues in your platform:
#        Your Composer dependencies require a PHP version ">= 8.3.0".
#
#    Hence pinning $PHP to the binary the site actually serves with.
#
# 2. composer.lock itself pins maennchen/zipstream-php 3.2.1, which requires
#    php-64bit ^8.3, pulled in by phpoffice/phpspreadsheet. Under PHP 8.2
#    composer refuses outright: "Your lock file does not contain a compatible
#    set of packages." So --ignore-platform-reqs is REQUIRED here, not
#    cosmetic - it is what has kept this deploy working. Do not remove it
#    without first either moving the site's pool to PHP 8.3 or relaxing
#    zipstream in the lock (composer require maennchen/zipstream-php:^2.1).
PHP=/usr/bin/php8.2
COMPOSER="$(command -v composer)"

cd /home/pos-4ugbp/pos.sherinhuts.com || exit 1

# Take the site down, and guarantee it comes back up however this exits.
$PHP artisan down --retry=60 || true
trap '$PHP artisan up || true' EXIT

git fetch origin production
git reset --hard origin/production

$PHP "$COMPOSER" install --no-interaction --prefer-dist --optimize-autoloader --no-dev --ignore-platform-reqs

# No npm step: this branch commits its compiled assets (public/css,
# public/js, public/themes, public/mix-manifest.json). Branch v3 does not -
# it needs `npm ci && npm run build`.

# No `php artisan migrate`: the live database already matches this branch.
# Its migrations table holds only 2 rows, so a migrate would attempt to
# recreate existing tables and fail partway through.

$PHP artisan optimize:clear
$PHP artisan config:cache
$PHP artisan route:cache
$PHP artisan view:cache

sudo service php8.2-fpm reload

$PHP artisan up

echo "🚀 Deployed $(git rev-parse --short HEAD) on $(git rev-parse --abbrev-ref HEAD)"
