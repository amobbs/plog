FROM php:5.6.10-fpm

RUN apt-get update && apt-get install --yes \
    && apt-get install --yes zip unzip \
    && docker-php-ext-install mbstring \
    && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php
    