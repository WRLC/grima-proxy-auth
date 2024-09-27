FROM php:8.1-fpm

RUN apt update \
    && apt install -y zip git libzip-dev libcurl3-dev libssl-dev libmemcached-dev


# Install memcached
RUN pecl install memcached \
    && docker-php-ext-enable memcached

# Install xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Install Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"

# Add ssh keys from secrets
RUN mkdir -p /root/.ssh
RUN ln -s /run/secrets/user_ssh_key /root/.ssh/id_rsa
