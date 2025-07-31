FROM php:8.1-apache

# Install system dependencies and PHP extensions in one layer
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip libzip-dev jq \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && a2enmod rewrite \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Get Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Remove problematic Laravel scripts from composer.json during build
RUN jq 'del(.scripts)' composer.json > composer.json.tmp && mv composer.json.tmp composer.json

# Install dependencies without any scripts
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-plugins

# Copy application code
COPY . .

# Configure Apache
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Set permissions and create required directories
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache \
    && mkdir -p /var/www/html/storage/framework/{cache,sessions,views} \
    && mkdir -p /var/www/html/storage/logs

EXPOSE 80
CMD ["apache2-foreground"]