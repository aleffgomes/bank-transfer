FROM php:8.1.2-fpm

ENV CFLAGS="$CFLAGS -D_GNU_SOURCE"

# Instala dependências do PHP e utilitários necessários
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libsodium-dev \
    libicu-dev \
    curl \
    zip \
    default-mysql-client \
    git \
    unzip

# Instala extensões PHP necessárias
RUN docker-php-ext-install \
    zip \
    intl \
    mysqli \
    sodium \
    sockets 

# Instala e habilita o Xdebug
RUN pecl install xdebug && docker-php-ext-enable xdebug
RUN echo "xdebug.mode=coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

COPY docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini

COPY docker/scripts /var/www/html/docker/scripts
RUN chmod +x /var/www/html/docker/scripts/*.sh

WORKDIR /var/www/html

COPY . .

RUN chown -R www-data:www-data /var/www/html/writable \
    && chmod -R 777 /var/www/html/writable 

RUN mkdir -p /var/www/html/writable/logs \
    && touch /var/www/html/writable/logs/init-app.log \
    && chown -R www-data:www-data /var/www/html/writable/logs

EXPOSE 9000

CMD ["php-fpm"]