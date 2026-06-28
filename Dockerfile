# ==========================================
# Stage 1: Install PHP Dependencies (Composer)
# ==========================================
FROM php:8.2-alpine AS backend-builder
WORKDIR /app

# Install system dependencies for Composer and extensions
RUN apk add --no-cache \
    git \
    unzip \
    libzip-dev

# Install Zip extension for Composer extraction
RUN docker-php-ext-install zip

# Copy Composer binary from official image
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Copy composer files
COPY composer.json composer.lock ./

# Install dependencies without running autoloader or scripts (for build cache)
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

# Copy the rest of the application files
COPY . .

# Generate the autoloader and run install scripts
RUN composer install --no-dev --optimize-autoloader --prefer-dist --no-interaction


# ==========================================
# Stage 2: Build Frontend Assets (Vite + Vue)
# ==========================================
FROM node:20-alpine AS frontend-builder
WORKDIR /app

# Copy package files and configuration
COPY package.json vite.config.js tailwind.config.js postcss.config.js ./
# Copy resources containing Vue templates/CSS
COPY resources ./resources
COPY public ./public

# Copy vendor directory from backend-builder (needed for Ziggy routing imports)
COPY --from=backend-builder /app/vendor ./vendor

# Install dependencies and build
RUN npm install --legacy-peer-deps && npm run build


# ==========================================
# Stage 3: Production Runtime (PHP-FPM + Nginx)
# ==========================================
FROM php:8.2-fpm-alpine
WORKDIR /var/www/html

# Install production system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    libzip \
    libpng \
    libjpeg-turbo \
    freetype \
    bash

# Install build dependencies for PHP extensions, install extensions, and clean up build dependencies
RUN apk add --no-cache --virtual .build-deps \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo_mysql bcmath gd zip opcache \
    && apk del .build-deps

# Copy configurations
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini

# Copy application code from backend builder
COPY --from=backend-builder --chown=www-data:www-data /app /var/www/html

# Copy built frontend assets from frontend builder
COPY --from=frontend-builder --chown=www-data:www-data /app/public/build /var/www/html/public/build

# Setup storage and cache directories permissions
RUN mkdir -p /var/www/html/storage/framework/cache/data \
             /var/www/html/storage/framework/sessions \
             /var/www/html/storage/framework/views \
             /var/www/html/storage/logs \
             /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Make entrypoint script executable
RUN chmod +x /var/www/html/docker/entrypoint.sh

# Expose port 8080 (handled by internal Nginx)
EXPOSE 8080

# Define entrypoint and default command
ENTRYPOINT ["/var/www/html/docker/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
