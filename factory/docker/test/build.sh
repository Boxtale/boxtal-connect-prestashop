#!/usr/bin/env bash

PS_VERSION=${1-1.7.3.4}
MULTISTORE=${2-0}

if [[ $(docker inspect -f {{.State.Running}} boxtal_connect_prestashop) = "false" ]]; then
    echo "boxtal_connect_prestashop docker container is not running"
    exit
fi

docker exec  -u docker boxtal_connect_prestashop /home/docker/factory/common/test/build.sh $PS_VERSION $MULTISTORE false
