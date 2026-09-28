#!/bin/sh
set -e

# Default PORT to 80 if not set (Render provides PORT dynamically e.g. 10000)
PORT="${PORT:-80}"

# Configure Apache listening port
echo "Listen ${PORT}" > /etc/apache2/ports.conf

# Setup .env file from .env.example if missing
if [ ! -f /var/www/html/.env ]; then
    cp /var/www/html/.env.example /var/www/html/.env
fi

# Ensure database directory and SQLite file exist
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    mkdir -p /var/www/html/database
    if [ ! -f /var/www/html/database/database.sqlite ]; then
        touch /var/www/html/database/database.sqlite
    fi
    chown -R www-data:www-data /var/www/html/database
    chmod -R 775 /var/www/html/database
fi

# Ensure storage directories exist with proper permissions
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Remove any stale cached manifests
rm -f /var/www/html/bootstrap/cache/*.php

# Discover packages now that autoloader is active
php artisan package:discover --ansi || true

# Generate application key if not set
if [ -z "$APP_KEY" ]; then
    echo "Generating application encryption key..."
    php artisan key:generate --force || true
fi

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force || true

# Run database seeders if enabled
if [ "${RUN_SEEDS:-true}" = "true" ]; then
    echo "Seeding database with initial data..."
    php artisan db:seed --force || true
fi

# Cache config, routes, and views for production performance
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "Starting Apache on port ${PORT}..."
exec apache2-foreground
