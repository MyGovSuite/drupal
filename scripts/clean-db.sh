#!/usr/bin/env bash

echo "Executing ${0}"

if [ "$( docker ps -a --filter name='mygov_mariadb' --format "{{ .ID }}" )" ] &&
   [ "$( docker container inspect -f '{{.State.Running}}' 'mygov_mariadb' )" != "true" ]; then

  printf "\nRemove mygov_mariadb container\n\n"
  docker rm "$(docker ps -a --filter name='mygov_mariadb' --format "{{ .ID }}")"

  printf "\nRemove drupal_mariadb-volume\n\n"
  docker volume rm drupal_mariadb-volume

  printf "\nExecution completed!"

else
  printf "Error: mygov_mariadb container is running!"
fi
