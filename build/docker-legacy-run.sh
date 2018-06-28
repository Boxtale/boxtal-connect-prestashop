#!/usr/bin/env bash

PHP_VERSION=${1-7.0}
PS_VERSION=${2-1.7.3.4}
PORT=${3-80}

docker run -di -p $PORT:80 --name "boxtal_prestashop_legacy" 890731937511.dkr.ecr.eu-west-1.amazonaws.com/boxtal-prestashop-legacy:$PHP_VERSION-$PS_VERSION
