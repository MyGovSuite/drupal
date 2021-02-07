#!/usr/bin/env bash

printf "Executing init.sh \n\n"

if [ "$( docker container inspect -f '{{.State.Running}}' 'mygov_php' )" == "true" ]; then

  printf "Composer install \n\n"
  docker exec "$(docker ps -a --filter name='mygov_php' --format "{{ .ID }}")" composer install --no-dev --prefer-dist -n

  printf "Update database \n\n"
  docker exec "$(docker ps -a --filter name='mygov_php' --format "{{ .ID }}")" drush updb -y

  printf "Synchronize configuration \n\n"
  docker exec "$(docker ps -a --filter name='mygov_php' --format "{{ .ID }}")" drush config:import -y

  printf "\nRestore development.services.yml from drupal-scaffold \n\n"
  git checkout -- web/sites/development.services.yml

  printf "Clear caches \n\n"
  docker exec "$(docker ps -a --filter name='mygov_php' --format "{{ .ID }}")" drush cache-rebuild -y

  printf "Execution completed! \n\n"

else
  printf "Error: mygov_php container is not running! \n\n"
fi
