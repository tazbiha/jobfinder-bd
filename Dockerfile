FROM php:8.2-apache

RUN docker-php-ext-install mysqli
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

WORKDIR /var/www/html