FROM php:7.4-apache
RUN apt-get update 
COPY docker/php.ini /var/www/php.ini
RUN mv "/var/www/php.ini" "$PHP_INI_DIR/php.ini"

RUN a2dissite 000-default.conf
COPY docker/apache2site.conf /etc/apache2/sites-available/apache2site.conf
RUN a2ensite apache2site.conf

RUN apt-get install -y libzip-dev
RUN docker-php-ext-install zip

COPY . /var/www/

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN COMPOSER=/var/www/composer.json composer install

# docker build -t adamrms .
# docker run -d -p 80:80 --name adamrms-container adamrms

# docker stop adamrms-container && docker rm adamrms-container

# docker stats