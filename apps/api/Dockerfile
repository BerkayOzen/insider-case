FROM php:8.5.2-fpm-alpine3.23

WORKDIR /var/www

RUN apk add --no-cache git unzip sqlite-dev \
    && docker-php-ext-install pdo pdo_sqlite

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install --no-interaction --prefer-dist

EXPOSE 8000

CMD ["./docker-entrypoint.sh"]
