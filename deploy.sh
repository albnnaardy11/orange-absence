#!/bin/bash

# 🍊 Orange Absence Deployment Script (Enterprise Standard)

echo "Starting deployment process..."

# 1. Maintenance Mode
echo "Entering maintenance mode..."
php artisan down || true

# 2. Update Code
# echo "Pulling latest changes from git..."
# git pull origin main

# 3. Security Check & Dependencies
echo "Installing dependencies..."
composer install --no-dev --optimize-autoloader
npm install
npm run build

# 4. Database Migrations
echo "Running database migrations..."
php artisan migrate --force

# 5. Production Optimization
echo "Optimizing application for performance..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan filament:cache-components || true

# 6. Cache Clearing
echo "Clearing general cache..."
php artisan cache:clear

# 7. Background Processing Monitoring
# echo "Restarting Laravel Horizon..."
# php artisan horizon:terminate || true

# 8. Exit Maintenance Mode
echo "Application back online!"
php artisan up

echo "Deployment finished successfully! 🚀"
