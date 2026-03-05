FROM php:8.2-apache

# Instalar dependencias para PostgreSQL y mPDF
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    libzip-dev \
    libxml2-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql pgsql gd zip bcmath

# 3. Habilitar extensión rewrite para Apache
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && a2enmod rewrite

# 3.1 Configurar Xdebug para desarrollo local
RUN echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.log=/tmp/xdebug.log" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Configurar el directorio de trabajo
WORKDIR /var/www/html

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar configuración de Apache
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

# Copiar el contenido del proyecto
COPY . /var/www/html/

# Instalar dependencias de PHP con Composer
RUN composer install --no-interaction --optimize-autoloader

# Ajustar permisos para Apache
RUN chown -R www-data:www-data /var/www/html

# Exponer el puerto 80
EXPOSE 80
