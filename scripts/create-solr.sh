#!/usr/bin/env bash

echo "Executing ${0}"

if [ "$( docker container inspect -f '{{.State.Running}}' 'mygov_solr' )" == "true" ]; then

  printf "Create SOLR core \n\n"
  docker exec -it $(docker ps --filter name='mygov_solr' --format "{{ .ID }}") make core=default -f /usr/local/bin/actions.mk

  printf "Execution completed!"

else
  printf "Error: mygov_solr container is not running!"
fi
