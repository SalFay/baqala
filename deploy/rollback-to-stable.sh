#!/usr/bin/env bash
#
# Roll production back to the last known-good commit.
#
#   Target: b13749c8 "Fix inventory table name in DashboardController"
#   This is the code that ran on pos.sherinhuts.com from 2026-01-07 until
#   2026-08-27, and the state restored on 2026-08-04. It is Laravel 8 +
#   webpack Mix, and it matches the CURRENT 23-table production database.
#
# Run as the site user:
#     ssh baqala-ploi
#     sudo -u pos-4ugbp -i bash
#     bash /home/pos-4ugbp/pos.sherinhuts.com/deploy/rollback-to-stable.sh
#
# Then, back as ploi:  sudo service php8.2-fpm reload
#
set -euo pipefail

SITE="/home/pos-4ugbp/pos.sherinhuts.com"
TARGET="b13749c842ee4c22162ee679fe6743e6bcc9313f"
STAMP="$(date +%F-%H%M)"

cd "$SITE"

echo "==> Preflight"
php -v | head -1
whoami
test -w . || { echo "FATAL: no write access here. Run as pos-4ugbp."; exit 1; }

echo
echo "==> Backing up .env and uploads"
cp -a .env "$HOME/env-backup-$STAMP"
if [ -d public/assets ]; then
  cp -a public/assets "$HOME/assets-backup-$STAMP"
fi
echo "    saved to $HOME/*-backup-$STAMP"

echo
echo "==> Database backup"
DB_NAME="$(grep -E '^DB_DATABASE=' .env | cut -d= -f2- | tr -d '"'"'"'')"
DB_USER="$(grep -E '^DB_USERNAME=' .env | cut -d= -f2- | tr -d '"'"'"'')"
echo "    dumping ${DB_NAME} as ${DB_USER} (password will be prompted)"
mysqldump -u "$DB_USER" -p "$DB_NAME" > "$HOME/db-backup-$STAMP.sql"
echo "    saved to $HOME/db-backup-$STAMP.sql"

echo
echo "==> Rolling code back to ${TARGET:0:10}"
# Objects are already local (see git reflog); no fetch required.
git -c safe.directory="$SITE" reset --hard "$TARGET"

echo
echo "==> Rebuilding PHP dependencies (Laravel 8)"
# Mandatory: vendor/ currently holds Laravel 11 packages from the v3 deploy.
/usr/bin/php8.2 "$(command -v composer)" install --no-interaction --prefer-dist --optimize-autoloader --no-dev

echo
echo "==> Clearing caches"
php artisan optimize:clear

echo
echo "==> Done. Deployed $(git -c safe.directory="$SITE" rev-parse --short HEAD)"
echo
echo "NOT run, deliberately:"
echo "  * php artisan migrate  - the database already matches this commit."
echo "                           Its migrations table has only 2 rows, so a"
echo "                           migrate would try to recreate live tables."
echo "  * npm install / build  - compiled assets are committed to this commit"
echo "                           (public/css, public/js, public/themes)."
echo
echo "Next, exit to the ploi user and run:"
echo "  sudo service php8.2-fpm reload"
echo "  curl -s -o /dev/null -w '%{http_code}\\n' https://pos.sherinhuts.com/login"
