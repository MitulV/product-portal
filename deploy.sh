#!/bin/bash
php artisan down
git pull origin main
composer install --optimize-autoloader --no-dev

# Check if npm is installed
if ! command -v npm &> /dev/null; then
    echo "ERROR: npm is not installed. Please install Node.js and npm first."
    echo "You can install Node.js using one of these methods:"
    echo "  - Using nvm: curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash"
    echo "  - Using package manager: yum install nodejs npm (or apt-get install nodejs npm)"
    php artisan up
    exit 1
fi

npm ci
npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up
echo "Deployment Finished!"
