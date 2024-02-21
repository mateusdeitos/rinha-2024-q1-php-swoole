FROM openswoole/swoole:latest-alpine

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN apk add --no-cache \
    && docker-php-ext-install pdo_pgsql

# Set working directory
WORKDIR /var/www

# Copy the current directory contents into the container at /var/www
COPY . /var/www

COPY ./composer*.json /var/www/

RUN composer install

CMD ["php", "server.php"]

