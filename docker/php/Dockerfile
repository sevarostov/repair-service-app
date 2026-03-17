FROM php:8.4-fpm AS php

RUN apt-get update \
    && docker-php-ext-install pdo pdo_mysql \
	&& apt-get install -y \
    unzip libpq-dev libzip-dev \
 	&& docker-php-ext-install zip exif \
 	&& docker-php-ext-configure exif \
	&& rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
ENTRYPOINT ["php-fpm","-F"]
