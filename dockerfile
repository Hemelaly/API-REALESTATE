FROM php:8.1-apache

# Copia o código para o container
COPY . /var/www/html/

# Instala extensões PHP, Composer, etc, se precisar
RUN docker-php-ext-install pdo pdo_mysql

EXPOSE 80
