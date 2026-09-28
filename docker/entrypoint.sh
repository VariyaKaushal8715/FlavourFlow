#!/bin/sh
set -e

# Default PORT to 80 if not provided
PORT="${PORT:-80}"

# Adjust Apache to listen on $PORT
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:${PORT}>/g" /etc/apache2/sites-available/000-default.conf

# Ensure database directory and sqlite file exist if using SQLite
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    mkdir -p /var/www/html/database
    if [ ! -f /var/www/html/database/database.sqlite ]; then
        touch /var/www/html/database/database.sqlite
    fi
    chown -R www-data:www-data /var/www/html/database
    chmod -R 775 /var/www/html/database
fi

# Ensure storage and bootstrap cache permissions
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Generate app key if not set
if [ -z "$APP_KEY" ]; then
    echo "APP_KEY is not set. Generating a new application key..."
    php artisan key:generate --force
fi

# Clear and cache configurations
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force

# Seed database if requested or enabled
if [ "${RUN_SEEDS:-true}" = "true" ]; then
    echo "Seeding database..."
    php artisan db:seed --force || true
fi

# Optimize Laravel for production
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "Starting Apache on port ${PORT}..."
exec apache2-foreground
