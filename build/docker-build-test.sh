#!/usr/bin/env bash

PS_VERSION=${1-1.7.3.3}

if [[ $(docker inspect -f {{.State.Running}} boxtal_prestashop) = "false" ]]; then
    echo "boxtal_prestashop docker container is not running"
    exit
fi

docker exec  -u docker boxtal_prestashop /home/docker/test/bin/build-test.sh $PS_VERSION false
