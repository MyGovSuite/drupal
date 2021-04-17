ARG PHP_TAG=8.9.0-debian-10-r0

FROM composer:2 as builder
COPY composer.json composer.lock /app/
WORKDIR /app
RUN composer install --no-dev --prefer-dist --no-interaction --ignore-platform-req=php --ignore-platform-req=ext-gd

USER root

FROM bitnami/drupal:$PHP_TAG
RUN rm -rf /opt/bitnami/drupal/*
COPY --from=builder /app /opt/bitnami/drupal
COPY web/modules /bitnami/drupal/modules
COPY web/themes /bitnami/drupal/themes
