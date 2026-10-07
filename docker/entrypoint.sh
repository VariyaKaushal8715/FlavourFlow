#!/bin/sh
set -e

# Default PORT to 80 if not set (Render sets PORT e.g. 10000)
PORT="${PORT:-80}"

# Configure Apache listening port
echo "Listen ${PORT}" > /etc/apache2/ports.conf

# Setup .env file from .env.example if missing
if [ ! -f /var/www/html/.env ]; then
    cp /var/www/html/.env.example /var/www/html/.env
fi

# If DB_CONNECTION is not explicitly mysql/pgsql, force sqlite defaults in .env
if [ -z "$DB_CONNECTION" ] || [ "$DB_CONNECTION" = "sqlite" ]; then
    sed -i "s/^DB_CONNECTION=.*/DB_CONNECTION=sqlite/g" /var/www/html/.env
    sed -i "s/^DB_HOST=/# DB_HOST=/g" /var/www/html/.env
    sed -i "s/^DB_PORT=/# DB_PORT=/g" /var/www/html/.env
    sed -i "s|^DB_DATABASE=.*|DB_DATABASE=/var/www/html/database/database.sqlite|g" /var/www/html/.env
    sed -i "s/^DB_USERNAME=/# DB_USERNAME=/g" /var/www/html/.env
    sed -i "s/^DB_PASSWORD=/# DB_PASSWORD=/g" /var/www/html/.env
fi

# Ensure LOG_CHANNEL outputs to stderr so it shows in Render logs
sed -i "s/^LOG_CHANNEL=.*/LOG_CHANNEL=stderr/g" /var/www/html/.env

# Update APP_URL and ASSET_URL with HTTPS
if [ -n "$RENDER_EXTERNAL_URL" ]; then
    sed -i "s|^APP_URL=.*|APP_URL=${RENDER_EXTERNAL_URL}|g" /var/www/html/.env
    echo "ASSET_URL=${RENDER_EXTERNAL_URL}" >> /var/www/html/.env
else
    sed -i "s|^APP_URL=.*|APP_URL=https://flavourflow.onrender.com|g" /var/www/html/.env
    echo "ASSET_URL=https://flavourflow.onrender.com" >> /var/www/html/.env
fi

# Ensure production environment
sed -i "s/^APP_ENV=.*/APP_ENV=production/g" /var/www/html/.env
sed -i "s/^APP_DEBUG=.*/APP_DEBUG=false/g" /var/www/html/.env

# Create SQLite database directory and file with full write permissions
mkdir -p /var/www/html/database
if [ ! -f /var/www/html/database/database.sqlite ]; then
    touch /var/www/html/database/database.sqlite
fi
chmod 777 /var/www/html/database
chmod 666 /var/www/html/database/database.sqlite
chown -R www-data:www-data /var/www/html/database

# Ensure storage directories exist with full permissions
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Remove any stale cached manifests
rm -f /var/www/html/bootstrap/cache/*.php

# Clear caches first
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Generate application key if missing
if ! grep -q "^APP_KEY=base64:" /var/www/html/.env; then
    echo "Generating application encryption key..."
    php artisan key:generate --force
fi

# Discover packages
php artisan package:discover --ansi || true

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force

# Run database seeders
echo "Seeding database..."
php artisan db:seed --force || true

# Cache config, routes, and views for production performance
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "Starting Apache on port ${PORT}..."
exec apache2-foreground
