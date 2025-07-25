FROM php:8.1-apache

# Instala extensões necessárias (pdo_mysql, etc)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    zip \
    git \
    libonig-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-install pdo pdo_mysql mysqli

# Ativa mod_rewrite do Apache
RUN a2enmod rewrite

# Permite .htaccess
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Copia os arquivos da aplicação
COPY . /var/www/html/

# Instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html/
RUN composer install --no-dev --optimize-autoloader
