ARG PHP_TAG=7.4

FROM wodby/drupal-php:$PHP_TAG

COPY composer.json composer.lock ./

USER root

RUN composer install --no-dev --prefer-dist --no-interaction

COPY web/modules/custom/ /var/www/html/web/modules/custom
COPY web/themes/custom/ /var/www/html/web/themes/custom

RUN chown -R wodby:wodby web/

USER wodby
