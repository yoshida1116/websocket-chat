FROM php:8.4-cli

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    unzip zip git libzip-dev \
    && docker-php-ext-install pdo pdo_mysql

COPY . .

# Laravel key
RUN php artisan key:generate || true

EXPOSE 9000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=9000"]
