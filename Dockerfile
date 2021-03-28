ARG PHP_TAG=7.4

FROM wodby/drupal-php:$PHP_TAG

COPY composer.json composer.lock ./

RUN composer install --no-dev --prefer-dist -n

COPY web/modules/custom/ /var/www/html/web/modules/custom
COPY web/themes/custom/ /var/www/html/web/themes/custom
