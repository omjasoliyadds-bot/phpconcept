FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    zip unzip git curl libzip-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy project
COPY . .

# Install Laravel dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Create app user
RUN useradd -G www-data,root -u 1000 -d /home/appuser appuser \
    && mkdir -p /home/appuser \
    && chown -R appuser:appuser /home/appuser

# Set permissions
RUN chown -R appuser:www-data /var/www \
    && chmod -R 775 storage bootstrap/cache

USER appuser

EXPOSE 9000

CMD ["php-fpm"]