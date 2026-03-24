FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    zip unzip git curl libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip

# Create non-root user
RUN useradd -G www-data,root -u 1000 -d /home/appuser appuser \
    && mkdir -p /home/appuser \
    && chown -R appuser:appuser /home/appuser

# Set working directory
WORKDIR /var/www

# Copy application
COPY . .

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

RUN chown -R appuser:www-data /var/www \
    && chmod -R 755 /var/www

USER appuser

EXPOSE 9000

CMD ["php-fpm"]