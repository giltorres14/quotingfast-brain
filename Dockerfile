# Use PHP 8.3 with Apache
FROM php:8.3-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    libzip-dev \
    zip \
    unzip \
    sqlite3 \
    libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite mbstring exif pcntl bcmath gd intl zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy the Laravel application from brain directory
COPY brain/ .

# Ensure .env file is present (copy from brain if needed)
COPY brain/.env .env

# Allow composer to run as root
ENV COMPOSER_ALLOW_SUPERUSER=1

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Set production environment variables BEFORE running artisan commands
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV APP_URL=https://lobster-app-rsicc.ondigitalocean.app
ENV DB_CONNECTION=sqlite
ENV DB_DATABASE=/var/www/html/database/database.sqlite
ENV APP_KEY=base64:le+1ceIdu/c0cXfW1TyldipruKEviBuiZYB2Z74vJhE=

# Create SQLite database file and directory
RUN mkdir -p /var/www/html/database \
    && touch /var/www/html/database/database.sqlite

# Ensure APP_KEY is set in .env file, then optimize Laravel
RUN echo "APP_KEY=base64:le+1ceIdu/c0cXfW1TyldipruKEviBuiZYB2Z74vJhE=" >> .env \
    && php artisan config:clear \
    && php artisan cache:clear \
    && php artisan route:clear \
    && php artisan config:cache

# Create Laravel storage directories and set permissions in one step
RUN mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views \
    && mkdir -p storage/app/public \
    && mkdir -p bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Enable Apache modules and sites
RUN a2enmod rewrite

# Copy Apache configuration and enable site
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
RUN a2ensite 000-default

# Create the logo directory and ensure it exists
RUN mkdir -p public/images

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]