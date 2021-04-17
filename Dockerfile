ARG PHP_TAG=8.9.0-debian-10-r0

FROM bitnami/drupal:$PHP_TAG

COPY composer.json composer.lock /opt/bitnami/drupal

USER root

RUN cd /opt/bitnami/drupal && \
    composer install --no-dev --prefer-dist --no-interaction

COPY web/modules/custom/ /opt/bitnami/drupal/web/modules/custom
COPY web/themes/custom/ /opt/bitnami/drupal/web/themes/custom
