FROM php:8.1.2-apache

ENV CFLAGS="$CFLAGS -D_GNU_SOURCE"

# PHP extensions/dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libsodium-dev \
    libicu-dev \
    curl \
    zip \
    default-mysql-client \
    git \
    unzip

RUN docker-php-ext-install \
    zip \
    intl \
    mysqli \
    sodium \
    sockets 

RUN pecl install xdebug && docker-php-ext-enable xdebug
RUN echo "xdebug.mode=coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# document root
COPY docker/config/000-default.conf /etc/apache2/sites-available/000-default.conf

# mod_rewrite
RUN a2enmod rewrite headers

# composer
RUN curl -sS getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer
RUN chmod +x /usr/local/bin/composer
RUN composer self-update

# Copy custom configurations PHP
COPY docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini

# Copy scripts
COPY docker/scripts /var/www/html/docker/scripts
RUN chmod +x /var/www/html/docker/scripts/*.sh

WORKDIR /var/www/html

# Copy application files
COPY . .

# Permissions
RUN chown -R www-data:www-data /var/www/html/writable \
    && chmod -R 777 /var/www/html/writable 

# Create log directory for initialization
RUN mkdir -p /var/www/html/writable/logs \
    && touch /var/www/html/writable/logs/init-app.log \
    && chown -R www-data:www-data /var/www/html/writable/logs

EXPOSE 80
EXPOSE 8080
EXPOSE 13000

ENTRYPOINT ["/var/www/html/docker/scripts/docker-entrypoint.sh"]