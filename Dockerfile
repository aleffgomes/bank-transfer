FROM php:8.1.2-fpm

ENV CFLAGS="$CFLAGS -D_GNU_SOURCE"

# Instala dependências do sistema e PHP
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libsodium-dev \
    libicu-dev \
    curl \
    zip \
    default-mysql-client \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Instala extensões PHP necessárias
RUN docker-php-ext-install \
    zip \
    intl \
    mysqli \
    sodium \
    sockets \
    pcntl

# Instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN pecl install xdebug && docker-php-ext-enable xdebug
RUN echo "xdebug.mode=coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

COPY docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini

WORKDIR /var/www/html

COPY composer.json composer.lock ./

RUN composer install --no-interaction --optimize-autoloader

COPY . .

COPY docker/scripts /var/www/html/docker/scripts
RUN chmod +x /var/www/html/docker/scripts/*.sh

RUN chown -R www-data:www-data /var/www/html/writable \
    && chmod -R 777 /var/www/html/writable 

RUN mkdir -p /var/www/html/writable/logs \
    && touch /var/www/html/writable/logs/log-$(date +%Y-%m-%d).log \
    && chown -R www-data:www-data /var/www/html/writable/logs

RUN chmod +x /var/www/html/docker/scripts/notification-processor.sh

EXPOSE 9000

CMD ["php-fpm"]