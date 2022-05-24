FROM phpdockerio/php80-fpm:latest
RUN apt-get update \
    && apt-get -y --no-install-recommends install php8.0-redis \
        git \
        php8.0-gd \
        php8.0-zip php8.0-intl php8.0-sqlite php8.0-tidy \
        php8.0-mbstring php8.0-xmlrpc php8.0-xsl php8.0-pgsql pwgen php8.0-sybase \
        php8.0-xdebug \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*
