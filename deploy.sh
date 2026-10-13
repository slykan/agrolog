#!/bin/bash
cd ~/agrolog
git reset --hard origin/main
git pull origin main
npm run build
/opt/cpanel/ea-php83/root/usr/bin/php artisan migrate --force
/opt/cpanel/ea-php83/root/usr/bin/php artisan cache:clear
/opt/cpanel/ea-php83/root/usr/bin/php artisan view:clear
/opt/cpanel/ea-php83/root/usr/bin/php artisan config:cache
/opt/cpanel/ea-php83/root/usr/bin/php artisan route:cache
/opt/cpanel/ea-php83/root/usr/bin/php artisan view:cache
echo "Deploy gotov!"
