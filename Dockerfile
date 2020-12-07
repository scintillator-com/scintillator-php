
###############################################
# TODO: tag from php version
# TODO: where is the PHP log?
# TODO: where is the PHP-FPM log?
#
# sudo docker build -t local/sci-php-fpm:7.4 .
# sudo docker run -d --name sci_php_fpm \
#   --expose 9000 \
#   -p 9000:9000 \
#   local/sci-php-fpm:7.4
###############################################

FROM php:7.4.13-fpm-alpine3.11

#ref: https://github.com/docker-library/php/issues/233
RUN apk add --no-cache --virtual .phpize-deps $PHPIZE_DEPS \
  && pecl install mongodb-1.9.0 \
	&& docker-php-ext-enable mongodb \
	&& apk del .phpize-deps \
	&& mv "${PHP_INI_DIR}/php.ini-production" "${PHP_INI_DIR}/php.ini"

# ref: https://hub.docker.com/_/php
# The default config can be customized by copying configuration files into the $PHP_INI_DIR/conf.d/ directory.

# COMPOSER:
#  && apk --no-cache add curl git openssh \
#  && curl -sSL https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

# PHP-FPM config:
# /usr/local/etc/php-fpm.conf
# /usr/local/etc/php-fpm.d/www.conf

#CEE: keep the normal paths so nginx can find it
COPY lib/ /var/www/lib/
COPY vendor/ /var/www/vendor/
COPY pub/ /var/www/html/

EXPOSE 9000
