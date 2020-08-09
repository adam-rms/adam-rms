FROM php:7.4-apache
#RUN apt-get update && apt-get install -y php-gd php-intl php-mysqli php-zip
COPY html/ /var/www/html/
COPY docker/php.ini /var/www/php.ini
RUN mv "/var/www/php.ini" "$PHP_INI_DIR/php.ini"

RUN a2dissite 000-default.conf
COPY docker/apache2site.conf /etc/apache2/sites-available/apache2site.conf
RUN a2ensite apache2site.conf

# docker build -t adamrms .
# docker run -d --name adamrms-run adamrms