FROM php:7.2-fpm as php

ENV PHP_OPCACHE_ENABLE=1
ENV PHP_OPCACHE_ENABLE_CLI=0
ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS=1
ENV PHP_OPCACHE_REVALIDATE_FREQ=1

RUN usermod -u 1000 www-data

# Install dependencies
RUN apt update && apt --no-install-recommends --no-install-suggests -y install \
    autoconf \
    curl \
    g++ \
    gifsicle \
    git \
    gnupg \
    jpegoptim \
    libpq-dev \
    librdkafka-dev \
    libzip-dev libwebp-dev libpng-dev libxpm-dev \
    optipng \
    pngquant \
    supervisor \
    unzip \
    zip \
    nginx \
    libcurl4-gnutls-dev \
    && apt clean  \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/* /var/www/html/* \
    && ln -sf /dev/stdout /var/log/unit.log

RUN apt-get update; \
    apt-get install -y libmagickwand-dev; \
    pecl install imagick; \
    docker-php-ext-enable imagick;

RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash
RUN apt --no-install-recommends --no-install-suggests -y install nodejs

# Install PHP extensions
RUN pecl channel-update pecl.php.net && \
    pecl install redis && \
    rm -rf /tmp/pear

# Install extensions
RUN docker-php-ext-install gd zip exif pcntl opcache sockets mysqli pdo pdo_mysql && \
    docker-php-ext-enable pdo_mysql

RUN docker-php-ext-enable redis
RUN docker-php-ext-enable redis.so

WORKDIR /var/www

COPY --chown=www-data .. .

COPY ./php/php.ini /usr/local/etc/php/php.ini
COPY ./php/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY ./php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY ./nginx/nginx.conf /etc/nginx/nginx.conf

# Supervisor configuration
COPY ./supervisor/config.conf /etc/supervisor/conf.d/supervisord.conf

COPY --from=composer:2.5.5 /usr/bin/composer /usr/bin/composer


#RUN  chmod -R 755 /var/www/storage
#RUN  chmod -R 755 /var/www/bootstrap

ENTRYPOINT [ "docker/entrypoint.sh" ]
