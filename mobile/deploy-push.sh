#!/usr/bin/env bash
set -euo pipefail
umask 077
project_root="${1:?Project root required}"
stage_root="${2:?Staging directory required}"
php_bin=/opt/cpanel/ea-php83/root/usr/bin/php
test "$project_root" = /home/agrolog/agrolog
test -f "$project_root/artisan"
test -f "$stage_root/firebase-service-account.json"
cd "$project_root"
files=(
    app/Models/MobilePushDevice.php
    app/Models/User.php
    app/Models/Obavijest.php
    app/Http/Controllers/MobilePushController.php
    app/Http/Controllers/Auth/AuthenticatedSessionController.php
    app/Support/MobilePush.php
    app/Support/WebPush.php
    app/Support/PushUpozorenja.php
    config/services.php
    routes/web.php
    resources/views/layouts/app.blade.php
    resources/views/profile/partials/obavijesti.blade.php
    database/migrations/2026_11_09_120000_create_mobile_push_devices.php
)
git diff --quiet -- "${files[@]}"
backup_root="/home/agrolog/deploy-backups/mobile-push-$(date -u +%Y%m%dT%H%M%SZ)"
mkdir -p "$backup_root"
cp -p .env "$backup_root/.env"
chmod 600 "$backup_root/.env"
for relative in "${files[@]}"; do
    "$php_bin" -l "$stage_root/$relative"
    if test -f "$project_root/$relative"; then
        mkdir -p "$backup_root/$(dirname "$relative")"
        cp -p "$project_root/$relative" "$backup_root/$relative"
    fi
done
mkdir -p storage/app/private
if test -f storage/app/private/firebase-service-account.json; then
    cp -p storage/app/private/firebase-service-account.json "$backup_root/firebase-service-account.json"
fi
install -m 600 "$stage_root/firebase-service-account.json" storage/app/private/firebase-service-account.json
# Validate configuration. Protected .env files are left untouched.
"$php_bin" "$stage_root/mobile/configure-firebase.php" "$project_root"
cp "$stage_root/database/migrations/2026_11_09_120000_create_mobile_push_devices.php" database/migrations/
chmod 644 database/migrations/2026_11_09_120000_create_mobile_push_devices.php
"$php_bin" artisan migrate --path=database/migrations/2026_11_09_120000_create_mobile_push_devices.php --force
for relative in "${files[@]}"; do
    mkdir -p "$project_root/$(dirname "$relative")"
    cp "$stage_root/$relative" "$project_root/$relative"
    chmod 644 "$project_root/$relative"
done
"$php_bin" artisan config:cache
"$php_bin" artisan route:cache
"$php_bin" artisan view:clear
"$php_bin" artisan route:list --path=push/mobile --no-ansi
printf '%s\n' "Backup: $backup_root" 'Mobile push deployment complete.'
