#!/bin/bash
php artisan down
git pull origin main
composer install --optimize-autoloader --no-dev
# Frontend assets are built locally and committed to the repository
# No need to run npm build on the server
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up
echo "Deployment Finished!"
