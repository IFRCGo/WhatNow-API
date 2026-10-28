FROM php:8.4-apache-bookworm

RUN php -r 'if (version_compare(PHP_VERSION, "8.4.1", "<")) { fwrite(STDERR, "PHP >= 8.4.1 is required by the locked dependencies\n"); exit(1); }'

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
RUN composer validate --strict --no-check-publish \
    && composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction \
    && composer check-platform-reqs --no-dev

ARG SWAGGER_URL
RUN : "${SWAGGER_URL:?SWAGGER_URL build argument is required}" \
    && SWAGGER_URL="$SWAGGER_URL" php artisan l5-swagger:generate

RUN mkdir -p storage/framework/sessions
RUN mkdir -p storage/framework/views
RUN mkdir -p storage/framework/cache
RUN chmod -R 775 storage
RUN chown -R www-data:www-data storage
