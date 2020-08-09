FROM php:7.4-apache
RUN apt-get update && apt-get install -y \
        php-gd \
        php-intl \
        php-mysqli \
        php-zip
COPY html/ /var/www/html/
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
ENV APACHE_DOCUMENT_ROOT /var/www/html/admin/
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf


# docker build -t adamrms .
# docker run -d --name adamrms-run adamrms