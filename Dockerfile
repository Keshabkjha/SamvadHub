# ─── Build Stage ──────────────────────────────────────────────────
FROM php:8.2-apache AS production

# Install PHP extensions required by SamvadHub
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    libzip-dev \
    unzip \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install gd mysqli pdo pdo_mysql opcache zip

# Enable Apache modules
RUN a2enmod rewrite headers expires deflate

# Configure Apache
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Configure PHP for production
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.memory_consumption=128'; \
    echo 'opcache.interned_strings_buffer=8'; \
    echo 'opcache.max_accelerated_files=4000'; \
    echo 'opcache.revalidate_freq=2'; \
    echo 'expose_php=Off'; \
    echo 'display_errors=Off'; \
    echo 'log_errors=On'; \
    echo 'error_log=/var/log/php_errors.log'; \
    echo 'upload_max_filesize=10M'; \
    echo 'post_max_size=12M'; \
    echo 'max_execution_time=30'; \
    echo 'session.cookie_httponly=1'; \
    echo 'session.cookie_samesite=Strict'; \
} >> /usr/local/etc/php/conf.d/samvadhub.ini

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY --chown=www-data:www-data . .

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install dependencies
RUN composer install --no-interaction --optimize-autoloader

# Ensure upload directories exist and are writable
RUN mkdir -p public/assets/images/posts public/assets/images/profile \
    && chown -R www-data:www-data public/assets/images \
    && chmod -R 755 public/assets/images

# Remove sensitive files from container
RUN rm -f pass.txt .env.example .gitignore *.sql config/*.json

# Expose port
EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD curl -f http://localhost/ || exit 1
