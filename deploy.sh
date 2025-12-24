#!/bin/bash
# Make this script executable (in case permissions were lost during git pull)
chmod +x "$0"

php artisan down
git pull origin main
composer install --optimize-autoloader --no-dev
php artisan migrate --force
php artisan optimize:clear
php artisan route:clear
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up
echo "Deployment Finished!"
