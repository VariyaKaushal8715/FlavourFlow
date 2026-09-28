# Stage 1: Build frontend assets with Node.js
FROM node:20-alpine AS frontend-builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# Stage 2: PHP 8.4 Apache application
FROM php:8.4-apache

# Install required system packages
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libicu-dev \
    sqlite3 \
    libsqlite3-dev \
    && rm -rf /var/lib/apt/lists/*

# Install official PHP extensions
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_mysql pdo_sqlite bcmath mbstring intl zip gd opcache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy custom Apache virtual host configuration
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf

# Install Composer binary
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application code
COPY . .

# Delete any stale local cached files
RUN rm -f bootstrap/cache/*.php

# Copy built frontend assets from frontend-builder stage
COPY --from=frontend-builder /app/public/build ./public/build

# Set Composer environment variable
ENV COMPOSER_ALLOW_SUPERUSER=1

# Install PHP dependencies without dev dependencies and without running artisan scripts during build
RUN composer install --no-dev --no-scripts --optimize-autoloader --no-interaction

# Prepare storage directories and script permissions
RUN mkdir -p storage/framework/cache/data \
             storage/framework/sessions \
             storage/framework/views \
             storage/logs \
             bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod +x /var/www/html/docker/entrypoint.sh

# Expose HTTP port
EXPOSE 80

# Execute entrypoint
ENTRYPOINT ["/var/www/html/docker/entrypoint.sh"]
