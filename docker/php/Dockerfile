FROM php:8.5-cli

RUN apt-get update
RUN apt-get install -y libzip-dev zip git wget gpg

RUN pear update-channels \
    && pecl update-channels \
    && pecl install xdebug

RUN docker-php-ext-enable xdebug

RUN echo "xdebug.mode=coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN git config --global --add safe.directory /app

# Create a new user
RUN useradd -m php

# Set the new user as the default user for the container
USER php