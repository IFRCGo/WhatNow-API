FROM php:7.4-apache

# Copia Composer desde la imagen oficial en una etapa de construcción separada
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN apt update && apt install -y \
    libzip-dev \
    zip

RUN docker-php-ext-install \
    zip \
    pdo \
    pdo_mysql \
    && a2enmod rewrite

COPY ./docker/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY ./ /var/www/html

# Instalar dependencias de Composer
WORKDIR /var/www/html
RUN composer install --no-dev --prefer-dist --optimize-autoloader

# Generar documentación de Swagger
RUN php artisan l5-swagger:generate

RUN mkdir -p storage/framework/sessions
RUN mkdir -p storage/framework/views
RUN mkdir -p storage/framework/cache
RUN chmod -R 775 storage
RUN chown -R www-data:www-data storage
