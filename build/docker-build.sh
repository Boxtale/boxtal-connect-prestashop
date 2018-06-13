#!/usr/bin/env bash

PHP_VERSION=${1-5.6}
PS_VERSION=${2-latest}
PORT=${3-80}

docker build . -t 890731937511.dkr.ecr.eu-west-1.amazonaws.com/boxtal-prestashop:$PHP_VERSION-$PS_VERSION-$PORT --build-arg PHP_VERSION=$PHP_VERSION --build-arg PS_VERSION=$PS_VERSION --build-arg PORT=$PORT
