# syntax=docker/dockerfile:1

################################################################################
# Composer Deps Stage
################################################################################

# Download dependencies as a separate step to take advantage of Docker's caching.
# Leverage bind mounts to composer.json and composer.lock to avoid having to copy 
# them into this layer. Also leverage a cache mount to /tmp/cache so that 
# subsequent builds don't have to re-download packages. Ignore platform requirements, 
# as we resolve those in the next stage

FROM composer:lts AS deps

WORKDIR /app

RUN --mount=type=bind,source=composer.json,target=composer.json \
    --mount=type=bind,source=composer.lock,target=composer.lock \
    --mount=type=cache,target=/tmp/cache \
    composer install --no-dev --no-interaction --ignore-platform-reqs

################################################################################
# PHP Build Stage
################################################################################

# A stage that contains the final, minimal running application, with full dependencies
# installed. This is based on _PHP 8.3_ with the apache web server.

# Useful documentation links:
# - https://github.com/docker-library/docs/tree/master/php#php-core-extensions
# - https://github.com/docker-library/docs/tree/master/php#how-to-install-more-php-extensions

FROM php:8.3-apache AS final

# Install PHP extensions
RUN apt-get update && apt-get install -y \
     libfreetype-dev \
     libjpeg62-turbo-dev \
     libpng-dev \
     libicu-dev \
 && rm -rf /var/lib/apt/lists/* \
     && docker-php-ext-configure gd --with-freetype --with-jpeg \
     && docker-php-ext-install -j$(nproc) gd mysqli intl

# Copy our php.ini file
COPY ./build-helpers/php.ini "$PHP_INI_DIR/php.ini"
# Copy Apache config
COPY ./build-helpers/apache.conf /etc/apache2/sites-available/000-default.conf

# Copy the app dependencies from the previous install stage.
COPY --from=deps app/vendor/ /var/www/html/vendor
# Copy the app files from the app directory.
COPY ./src /var/www/html/adam-rms

# Switch to the base image non-privileged user that the app will run under.
USER www-data
