# ---------- Frontend: build Vite assets ----------
FROM node:22-alpine AS frontend
WORKDIR /app
COPY package.json .npmrc ./
RUN npm install --ignore-scripts
COPY resources ./resources
COPY vite.config.js ./
RUN npm run build

# ---------- PHP runtime ----------
FROM php:8.4-cli-bookworm

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    PHP_OPCACHE_ENABLE=0

# System deps + PHP extensions required by Laravel 13 / Filament 3
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip curl \
        libsqlite3-dev libzip-dev libicu-dev libonig-dev libxml2-dev \
    && docker-php-ext-install -j$(nproc) \
        pdo_sqlite bcmath mbstring xml dom ctype fileinfo zip intl \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install PHP deps first (better layer caching)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --prefer-dist --no-scripts

# App source + compiled frontend assets
COPY . .
COPY --from=frontend /app/public/build ./public/build
RUN composer dump-autoload --optimize \
    && mkdir -p /data storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chmod -R ug+w storage bootstrap/cache \
    && chmod +x docker-entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["./docker-entrypoint.sh"]

# Default: web server. Overridden per-service in docker-compose.yml
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
