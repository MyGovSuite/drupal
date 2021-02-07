#!/usr/bin/env bash

echo "Executing ${0}"

printf "Compile SASS \n\n"

docker run --rm -v `pwd`:`pwd` -w `pwd` -e SASS_FILE=web/themes/custom/mygov_theme/scss/style.scss -e SASS_OUTPUT_PATH=web/themes/custom/mygov_theme/css/style.css mygov/sass:1.0
