FROM php:8.2-cli
WORKDIR /var/www
COPY . .
RUN apt-get update \
    && apt-get install -y libpq-dev libzip-dev git unzip \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && docker-php-ext-install sockets \
    && docker-php-ext-install bcmath \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install
EXPOSE 80
CMD ["php", "-S", "0.0.0.0:8080", "-t", "public"]
