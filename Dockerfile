
FROM php:7.4.13-fpm-alpine3.11
RUN apt-get update && apt-get install -y php-mongodb \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb

# ref: https://hub.docker.com/_/php
# The default config can be customized by copying configuration files into the $PHP_INI_DIR/conf.d/ directory.

COPY lib/ /var/www/lib/
COPY vendor/ /var/www/vendor/
COPY pub/ /var/www/html/

#TODO:  React?

