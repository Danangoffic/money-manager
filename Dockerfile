FROM php:8.2-fpm-alpine AS base

RUN apk add --no-cache nginx supervisor curl gettext mysql mysql-client \
    && docker-php-ext-install pdo pdo_mysql opcache

COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/nginx.conf /etc/nginx/nginx.conf.template
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

WORKDIR /var/www/html

# --- Build stage ---
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# --- Composer stage ---
FROM php:8.2-cli-alpine AS composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

# --- Final stage ---
FROM base

COPY --chown=www-data:www-data . .
COPY --chown=www-data:www-data --from=composer /app/vendor ./vendor
COPY --chown=www-data:www-data --from=frontend /app/public/build ./public/build

RUN mkdir -p /run/mysqld /var/lib/mysql \
    && chown -R mysql:mysql /run/mysqld /var/lib/mysql

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
