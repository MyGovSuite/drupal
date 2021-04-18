ARG PHP_TAG=8.9.0-debian-10-r0

FROM composer:2 as builder
COPY composer.json composer.lock /app/
WORKDIR /app
RUN composer install --no-dev --prefer-dist --no-interaction --ignore-platform-req=php --ignore-platform-req=ext-gd

USER root

FROM bitnami/drupal:$PHP_TAG
RUN rm -rf /opt/bitnami/drupal/*
COPY --from=builder /app /opt/bitnami/drupal
COPY web/modules /opt/bitnami/drupal/web/modules
COPY web/themes /opt/bitnami/drupal/web/themes
COPY load.environment.php /opt/bitnami/drupal/load.environment.php
COPY docker/overrides/drupal-vhost.conf /opt/bitnami/apache/conf/vhosts/drupal-vhost.conf
COPY docker/overrides/drupal-https-vhost.conf /opt/bitnami/apache/conf/vhosts/drupal-https-vhost.conf
