FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    nginx \
    libpq-dev \
    libzip-dev \
    libonig-dev \
    libicu-dev \
    unzip \
    git \
    && docker-php-ext-install pdo_pgsql pgsql mbstring intl zip bcmath \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/php/nginx.conf /etc/nginx/sites-available/default
COPY docker/php/entrypoint.sh /entrypoint.sh
RUN sed -i 's/\r$//' /entrypoint.sh && chmod +x /entrypoint.sh

WORKDIR /var/www/html/app

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
