ARG PHP_TAG=7.4

FROM wodby/drupal-php:$PHP_TAG

COPY composer.json composer.lock ./

RUN composer install --no-dev --prefer-dist -n
