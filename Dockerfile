FROM php:8.2-fpm-alpine

LABEL maintainer="Gop Gio Volunteer Team"
LABEL description="PHP 8.2-FPM container for Gop Gio Volunteer Platform"

# Install system dependencies & PHP extensions required for PDO MySQL, GD, Zip, Intl, Mbstring
RUN apk add --no-cache \
    bash \
    curl \
    git \
    unzip \
    icu-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    ttf-dejavu \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        mbstring \
        gd \
        zip \
        intl \
        opcache

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy custom PHP configuration
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

WORKDIR /var/www/html

# Copy composer files first for layer caching
COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-interaction --optimize-autoloader --no-scripts || true

# Copy application source
COPY . /var/www/html

# Ensure writable permissions for storage and uploads
RUN mkdir -p /var/www/html/storage/logs \
             /var/www/html/storage/certificates \
             /var/www/html/storage/cache \
             /var/www/html/public/uploads/avatars \
             /var/www/html/public/uploads/events \
             /var/www/html/public/uploads/organizations \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/public/uploads \
    && chmod -R 775 /var/www/html/storage /var/www/html/public/uploads

EXPOSE 9000

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD php-fpm -t || exit 1

CMD ["php-fpm"]
