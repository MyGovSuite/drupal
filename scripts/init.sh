#!/usr/bin/env bash

echo "Executing ${0}"

if [ "$( docker container inspect -f '{{.State.Running}}' 'mygov_php' )" == "true" ]; then

  printf "Composer install \n\n"
  docker exec "$(docker ps -a --filter name='mygov_php' --format "{{ .ID }}")" composer install --no-dev --prefer-dist -n

  printf "Update database \n\n"
  docker exec "$(docker ps -a --filter name='mygov_php' --format "{{ .ID }}")" drush updb -y

  printf "Synchronize configuration \n\n"
  docker exec "$(docker ps -a --filter name='mygov_php' --format "{{ .ID }}")" drush config:import -y

  printf "Clear caches \n\n"
  docker exec "$(docker ps -a --filter name='mygov_php' --format "{{ .ID }}")" drush cache-rebuild -y

  printf "Execution completed!"

else
  printf "Error: mygov_php container is not running!"
fi
