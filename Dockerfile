FROM php:7.4-apache

RUN set -ex; \
  { \
    echo "; Cloud Run enforces memory & timeouts"; \
    echo "memory_limit = -1"; \
    echo "max_execution_time = 0"; \
    echo "; File upload at Cloud Run network limit"; \
    echo "upload_max_filesize = 32M"; \
    echo "post_max_size = 32M"; \
    echo "; Configure Opcache for Containers"; \
    echo "opcache.enable = On"; \
    echo "opcache.validate_timestamps = Off"; \
    echo "; Configure Opcache Memory (Application-specific)"; \
    echo "opcache.memory_consumption = 32"; \
  } > "$PHP_INI_DIR/conf.d/cloud-run.ini"
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

#COPY docker/php.ini /var/www/php.ini
#RUN mv "/var/www/php.ini" "$PHP_INI_DIR/php.ini"

RUN a2dissite 000-default.conf
COPY docker/apache2site.conf /etc/apache2/sites-available/apache2site.conf
RUN a2ensite apache2site.conf
RUN a2dismod autoindex -f

RUN apt-get update
RUN apt-get install -y \
		libfreetype6-dev \
		libjpeg62-turbo-dev \
		libpng-dev \
		libzip-dev \
		libonig-dev \
		zlib1g-dev \
		libicu-dev \
		unzip \
	&& docker-php-ext-configure gd --with-freetype --with-jpeg \
	&& docker-php-ext-install -j$(nproc) gd
	&& apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*
RUN docker-php-ext-install zip
RUN docker-php-ext-install mbstring
RUN docker-php-ext-install mysqli
RUN docker-php-ext-install intl
RUN docker-php-ext-install gd
RUN docker-php-ext-install -j "$(nproc)" opcache

COPY . /var/www/
#COPY .env /var/www/.env

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
WORKDIR /var/www
RUN composer install
#RUN chown -R www-data:www-data html/admin/common/twigCache



# To get in container - docker exec -t -i adamrms-container /bin/bash

# docker stats

