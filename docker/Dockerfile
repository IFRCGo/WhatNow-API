# PHP 8.3.36+ includes security patches for CVE-2026-109618 and CVE-2026-109619 (PHP2Shell)
FROM php:8.3.36-apache

RUN php -r 'if (version_compare(PHP_VERSION, "8.3.36", "<")) { fwrite(STDERR, "PHP >= 8.3.36 is required to patch CVE-2026-109618 and CVE-2026-109619\n"); exit(1); }'

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN apt update && apt install -y \
    libzip-dev \
    unzip \
    zip

RUN docker-php-ext-install \
    zip \
    pdo \
    pdo_mysql \
    && a2enmod rewrite

COPY ./docker/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY ./ /var/www/html

WORKDIR /var/www/html
RUN composer install --no-dev --prefer-dist --optimize-autoloader

RUN php artisan l5-swagger:generate

RUN mkdir -p storage/framework/sessions
RUN mkdir -p storage/framework/views
RUN mkdir -p storage/framework/cache
RUN chmod -R 775 storage
RUN chown -R www-data:www-data storage
