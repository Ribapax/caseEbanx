FROM php:8.4-apache

RUN docker-php-ext-install pdo pdo_mysql

# Habilitar mod_rewrite no Apache
RUN a2enmod rewrite

COPY ./public /var/www/html

EXPOSE 80

