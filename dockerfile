FROM php:8.1-apache

# Ativar o mod_rewrite
RUN a2enmod rewrite

# Permitir que .htaccess funcione
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Copiar código da aplicação
COPY . /var/www/html/

# Instalar o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Instalar dependências PHP
WORKDIR /var/www/html/
RUN composer install --no-dev --optimize-autoloader
