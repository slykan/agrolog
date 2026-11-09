#!/usr/bin/env bash
# Targeted deployment; intentionally does not run the repository's git reset/pull script.
set -euo pipefail
umask 077
project_root="${1:?Project root required}"
stage_root="${2:?Staging directory required}"
php_bin=/opt/cpanel/ea-php83/root/usr/bin/php
test -f "$project_root/artisan"
test -f "$stage_root/app/Http/Controllers/Auth/MobileGoogleController.php"
cd "$project_root"
git diff --quiet -- app/Http/Controllers/Auth/SocialAuthController.php routes/auth.php resources/views/partials/favicon.blade.php resources/views/partials/instaliraj.blade.php
backup_root="/home/agrolog/deploy-backups/mobile-google-$(date -u +%Y%m%dT%H%M%SZ)"
mkdir -p "$backup_root"
files=(
    app/Http/Controllers/Auth/MobileGoogleController.php
    app/Http/Controllers/Auth/SocialAuthController.php
    app/Support/MobileGoogleLogin.php
    routes/auth.php
    resources/views/auth/mobile-google-bridge.blade.php
    resources/views/auth/mobile-google-finish.blade.php
    resources/views/auth/mobile-google-prepare.blade.php
    resources/views/partials/favicon.blade.php
    resources/views/partials/instaliraj.blade.php
    database/migrations/2026_11_09_100000_create_mobile_google_logins.php
)
for relative in "${files[@]}"; do
    test -f "$stage_root/$relative"
    "$php_bin" -l "$stage_root/$relative"
    if test -f "$project_root/$relative"; then
        mkdir -p "$backup_root/$(dirname "$relative")"
        cp -p "$project_root/$relative" "$backup_root/$relative"
    fi
done
printf '%s\n' "Backup: $backup_root"
# Schema first. Only this additive migration is allowed to run.
cp "$stage_root/database/migrations/2026_11_09_100000_create_mobile_google_logins.php" "$project_root/database/migrations/"
chmod 644 "$project_root/database/migrations/2026_11_09_100000_create_mobile_google_logins.php"
"$php_bin" artisan migrate --path=database/migrations/2026_11_09_100000_create_mobile_google_logins.php --force
for relative in "${files[@]}"; do
    mkdir -p "$project_root/$(dirname "$relative")"
    cp "$stage_root/$relative" "$project_root/$relative"
    chmod 644 "$project_root/$relative"
done
"$php_bin" artisan route:cache
"$php_bin" artisan view:clear
"$php_bin" artisan route:list --path=auth/mobile/google --no-ansi
printf '%s\n' 'Mobile Google deployment complete.'
