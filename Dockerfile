FROM mygov/drupal-php:7.4

COPY composer.json composer.lock ./

RUN composer install --no-dev --prefer-dist -n