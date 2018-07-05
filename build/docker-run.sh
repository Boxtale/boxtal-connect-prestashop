#!/usr/bin/env bash

PHP_VERSION=${1-5.6}
PS_VERSION=${2-latest}
PORT=${3-80}

if [ -z "$APIURL" ]; then
    APIURL=https://api.boxtal.com
fi

docker run -di -p $PORT:80 -e APIURL=$APIURL --name "boxtal_prestashop" 890731937511.dkr.ecr.eu-west-1.amazonaws.com/boxtal-prestashop:$PHP_VERSION-$PS_VERSION
