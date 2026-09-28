# Stage 1: Build frontend assets using Node.js
FROM node:20-alpine AS frontend-builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# Stage 2: PHP 8.4 Apache application
FROM php:8.4-apache

# Install system dependencies and PHP extensions installer
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libicu-dev \
    sqlite3 \
    libsqlite3-dev \
    && rm -rf /var/lib/apt/lists/*

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_mysql pdo_sqlite bcmath mbstring intl zip gd opcache

# Enable Apache rewrite module
RUN a2enmod rewrite

# Copy custom Apache virtual host configuration
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Copy built frontend assets from frontend builder stage
COPY --from=frontend-builder /app/public/build ./public/build

# Set environment variable for Composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# Install PHP dependencies without dev packages
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Setup permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod +x /var/www/html/docker/entrypoint.sh

# Expose default HTTP port
EXPOSE 80

# Run entrypoint script
ENTRYPOINT ["/var/www/html/docker/entrypoint.sh"]
