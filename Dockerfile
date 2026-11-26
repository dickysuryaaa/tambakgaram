# syntax=docker/dockerfile:1

# ---- Tahap 1: dependensi Composer (tanpa paket dev) ----
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --no-scripts \
    --prefer-dist --optimize-autoloader --ignore-platform-reqs
COPY . .
RUN composer dump-autoload --no-dev --optimize --no-scripts --ignore-platform-reqs

# ---- Tahap 2: image runtime (PHP-FPM + Nginx dalam satu container) ----
FROM php:8.3-fpm-alpine

RUN apk add --no-cache nginx \
 && docker-php-ext-install -j"$(nproc)" pdo_mysql opcache \
 && rm -rf /usr/local/etc/php-fpm.d/* /etc/nginx/http.d/* /tmp/*

COPY docker/php.ini /usr/local/etc/php/conf.d/zz-simtag.ini
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/simtag.conf
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

WORKDIR /var/www/html
COPY --from=vendor --chown=www-data:www-data /app ./

# Satu-satunya jalur tulis: cache view/config yang bersifat sementara per pod.
# Session, cache, dan data bisnis tetap di shared MySQL.
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh \
 && chmod +x /usr/local/bin/entrypoint.sh \
 && mkdir -p storage/framework/views storage/framework/cache storage/logs bootstrap/cache \
 && chown -R www-data:www-data storage bootstrap/cache \
 && php artisan view:cache \
 && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
