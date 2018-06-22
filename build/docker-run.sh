#!/usr/bin/env bash

PHP_VERSION=${1-5.6}
PS_VERSION=${2-latest}

docker run -di -p 80:80 --name "boxtal_prestashop" 890731937511.dkr.ecr.eu-west-1.amazonaws.com/boxtal-prestashop:$PHP_VERSION-$PS_VERSION
