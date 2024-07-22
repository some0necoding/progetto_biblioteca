# development image
FROM php:8.3.9-fpm AS dev

# add local configurations
COPY ./app/php.ini "$PHP_INI_DIR/conf.d"

# install postgresql binds
RUN apt-get update && apt-get install -y \
		libpq-dev \
		libpq5 \
	&& docker-php-ext-install -j$(nproc) pgsql

# production image
FROM dev AS prod

# load production config file
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# sources
WORKDIR /var/www/html
COPY ./app/public ./public
COPY ./app/src ./src
