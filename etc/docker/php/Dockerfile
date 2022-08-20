FROM composer:2.2.5 as composer
FROM php:8.1-fpm-alpine as base

ADD https://php.hernandev.com/key/php-alpine.rsa.pub /etc/apk/keys/php-alpine.rsa.pub

RUN apk --update-cache add ca-certificates && \
    echo "https://php.hernandev.com/v3.15/php-8.1" >> /etc/apk/repositories && \
    apk add --update --no-cache  \
    postgresql-libs postgresql-dev \
    php-mbstring~=8.1 \
    php-phar~=8.1 \
    php-xdebug=~8.1

RUN docker-php-ext-install pdo_pgsql

COPY --from=composer /usr/bin/composer /usr/local/bin/composer