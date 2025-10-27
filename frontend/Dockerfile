FROM php:8.1-apache

# Install PHP extensions and required packages
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_mysql \
    && a2enmod rewrite headers

# Configure PHP
RUN echo "error_reporting = E_ALL" >> /usr/local/etc/php/conf.d/docker-php-ext-errors.ini \
    && echo "display_errors = On" >> /usr/local/etc/php/conf.d/docker-php-ext-errors.ini

# Configure Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

WORKDIR /var/www/html

# Apache configuration will be mounted from host
# Files will be mounted from host at runtime

EXPOSE 80

CMD ["apache2-foreground"]