#!/usr/bin/env bash
# One-time recovery from the old reset-before-fetch deployment.
set -euo pipefail
umask 077
cd /home/agrolog/agrolog
git diff --quiet
git diff --cached --quiet
git merge-base --is-ancestor HEAD origin/main
backup="/home/agrolog/deploy-backups/recover-mobile-$(date -u +%Y%m%dT%H%M%SZ)"
mkdir -p "$backup"
paths=(
    app/Http/Controllers/Auth/MobileGoogleController.php
    app/Http/Controllers/MobilePushController.php
    app/Models/MobilePushDevice.php
    app/Support/MobileGoogleLogin.php
    app/Support/MobilePush.php
    database/migrations/2026_11_09_100000_create_mobile_google_logins.php
    database/migrations/2026_11_09_120000_create_mobile_push_devices.php
    resources/views/auth/mobile-google-bridge.blade.php
    resources/views/auth/mobile-google-finish.blade.php
    resources/views/auth/mobile-google-prepare.blade.php
)
for path in "${paths[@]}"; do
    # Move only the known untracked mobile files that now exist in the repository.
    if test -f "$path" && ! git ls-files --error-unmatch -- "$path" >/dev/null 2>&1; then
        git cat-file -e "origin/main:$path"
        mkdir -p "$backup/$(dirname "$path")"
        mv -- "$path" "$backup/$path"
    fi
done
git merge --ff-only origin/main
php_bin=/opt/cpanel/ea-php83/root/usr/bin/php
"$php_bin" artisan migrate --force
"$php_bin" artisan config:cache
"$php_bin" artisan route:cache
"$php_bin" artisan view:clear
"$php_bin" artisan route:list --path=auth/mobile --no-ansi
"$php_bin" artisan route:list --path=push/mobile --no-ansi
printf '%s\n' "Recovery backup: $backup"
