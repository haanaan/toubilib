FROM php:8.2-cli
WORKDIR /var/www/html
COPY . /var/www/html
RUN apt-get update \
    && apt-get install -y git unzip \
    && docker-php-ext-install sockets bcmath \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install
CMD ["php", "consume_notifications.php"]
