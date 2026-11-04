#!/bin/bash
cd ~/agrolog
git reset --hard origin/main
git pull origin main
npm run build
/opt/cpanel/ea-php83/root/usr/bin/php artisan migrate --force
# registar sredstava iz FIS-a (osvježava se i tjedno preko schedulera); pad FIS-a ne zaustavlja deploy
/opt/cpanel/ea-php83/root/usr/bin/php artisan szb:registar || echo "FIS registar nije osvježen"
/opt/cpanel/ea-php83/root/usr/bin/php artisan cache:clear
/opt/cpanel/ea-php83/root/usr/bin/php artisan view:clear
/opt/cpanel/ea-php83/root/usr/bin/php artisan config:cache
/opt/cpanel/ea-php83/root/usr/bin/php artisan route:cache
/opt/cpanel/ea-php83/root/usr/bin/php artisan view:cache
echo "Deploy gotov!"
