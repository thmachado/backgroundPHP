FROM composer:lts AS deps

WORKDIR /app

RUN --mount=type=bind,source=composer.json,target=composer.json \
    --mount=type=bind,source=composer.lock,target=composer.lock \
    --mount=type=cache,target=/tmp/cache \
    composer install --no-interaction

FROM php:8.3-fpm AS final

RUN apt-get update && apt-get install -y \
    nginx \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo pdo_pgsql \
    && docker-php-ext-enable pdo_pgsql

COPY docker/nginx.conf /etc/nginx/conf.d/default.conf

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY ./ /var/www/html
COPY --from=deps /app/vendor /var/www/html/vendor

RUN chown -R www-data:www-data /var/www/html

WORKDIR /var/www/html
EXPOSE 80

CMD bash -c "php-fpm -D && nginx -g 'daemon off;'"
