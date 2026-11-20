FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
        git \
        unzip \
        libzip-dev \
        oniguruma-dev \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        zip \
        bcmath \
        pcntl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Only matters in production — docker-compose.yml bind-mounts the repo over
# this in dev, shadowing everything copied/installed below, so dev always
# runs whatever's on the host instead (including its own separately
# installed vendor/, with dev dependencies).

# Copied and installed separately from the rest of the app so this layer
# only re-runs when composer.json/composer.lock actually change, instead of
# on every single code change.
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

COPY . .

RUN composer dump-autoload --optimize \
    && chown -R www-data:www-data storage bootstrap/cache
