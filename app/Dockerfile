FROM php:8.3.9-fpm

COPY ./php.ini "$PHP_INI_DIR/php.ini"

RUN apt-get update && apt-get install -y \
		libpq-dev \
		libpq5 \
	&& docker-php-ext-install -j$(nproc) pgsql
