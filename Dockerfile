FROM php:8.2-fpm-alpine AS base

RUN apk add --no-cache nginx supervisor curl \
    && docker-php-ext-install pdo pdo_mysql opcache

COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf

WORKDIR /var/www/html

# --- Build stage ---
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# --- Composer stage ---
FROM composer:2 AS composer
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

# --- Final stage ---
FROM base

COPY --chown=www-data:www-data . .
COPY --chown=www-data:www-data --from=composer /app/vendor ./vendor
COPY --chown=www-data:www-data --from=frontend /app/public/build ./public/build

RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
