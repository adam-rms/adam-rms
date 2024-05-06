FROM php:8-fpm

COPY ./php.ini /var/www/php.ini
RUN mv "/var/www/php.ini" "$PHP_INI_DIR/php.ini"

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
		git \
	&& docker-php-ext-configure gd --with-freetype --with-jpeg \
	&& docker-php-ext-install -j$(nproc) gd \
	&& apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*
RUN docker-php-ext-install zip
RUN docker-php-ext-install mbstring
RUN docker-php-ext-install mysqli
RUN docker-php-ext-install intl
RUN docker-php-ext-install gd
RUN docker-php-ext-install -j "$(nproc)" opcache
RUN docker-php-ext-install pdo pdo_mysql  # Required for Phyinx

COPY . /var/www/

RUN git log --pretty=\"%h\" -n1 HEAD > /var/www/src/version/COMMIT.txt
RUN git log --pretty=\"%H\" -n1 HEAD > /var/www/src/version/COMMITFULL.txt
RUN git describe --tags --abbrev=0 > /var/www/src/version/TAG.txt

RUN chmod +x /var/www/migrate.sh

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
WORKDIR /var/www
RUN composer install --optimize-autoloader --no-dev

ENTRYPOINT ["/var/www/migrate.sh"]