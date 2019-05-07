#!/usr/bin/env bash

PHP_VERSION=${1-5.6}
PS_VERSION=${2-1.7.3.4}
MULTISTORE=${3-0}

if [ ! -d "vendor" ]; then
  composer install --no-scripts --no-autoloader
fi

if [ ! -d "node_modules" ]; then
  npm install
fi

if [[ $MULTISTORE = "1" ]]; then
  APPEND="-multistore"
fi

docker build . -t 890731937511.dkr.ecr.eu-west-1.amazonaws.com/boxtal-prestashop:$PHP_VERSION-$PS_VERSION$APPEND --build-arg PHP_VERSION=$PHP_VERSION --build-arg PS_VERSION=$PS_VERSION --build-arg MULTISTORE=$MULTISTORE
