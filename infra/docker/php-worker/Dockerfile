FROM php:8.2-cli-alpine

RUN apk add --no-cache \
    ffmpeg \
    libzip-dev \
    linux-headers \
    $PHPIZE_DEPS

RUN docker-php-ext-install -j$(nproc) pdo_mysql pcntl bcmath

RUN pecl install redis && docker-php-ext-enable redis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

CMD ["php", "artisan", "queue:work", "redis", "--queue=video-conversion,default", "--tries=3", "--timeout=3600"]
