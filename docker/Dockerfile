#
FROM php:7.4-apache

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('sha384', 'composer-setup.php') === 'dac665fdc30fdd8ec78b38b9800061b4150413ff2e3b6f88543c636f7cd84f6db9189d43a81e5503cda447da73c7e5b6') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/local/bin/composer

RUN apt update && apt install -y \
    libzip-dev \
    zip


COPY ./docker/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY ./ /var/www/html

RUN docker-php-ext-install \
    zip \
    pdo \
    pdo_mysql \
    && a2enmod rewrite


RUN mkdir -p storage/framework/sessions
RUN mkdir -p storage/framework/views
RUN mkdir -p storage/framework/cache
RUN chmod -R 775 storage
RUN chown -R www-data:www-data storage

