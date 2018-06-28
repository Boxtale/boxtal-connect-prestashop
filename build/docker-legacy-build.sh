#!/usr/bin/env bash

PHP_VERSION=${1-7.0}
PS_VERSION=${2-1.7.3.4}
BOXTAL_LOGIN=${3}
BOXTAL_PWD=${4}

#rm -rf legacy
#mkdir -p legacy
#git clone git@git.boxtale.net:boxtale/Prestashop.git legacy
cd legacy
LATEST_TAG=`git describe --tags $(git rev-list --tags --max-count=1)`
LATEST_MAJOR_TAG=${LATEST_TAG:0:5}
git fetch && git fetch --tags
git checkout tags/$LATEST_MAJOR_TAG
cd ../

docker build . -f Dockerfile-legacy -t 890731937511.dkr.ecr.eu-west-1.amazonaws.com/boxtal-prestashop-legacy:$PHP_VERSION-$PS_VERSION --build-arg PHP_VERSION=$PHP_VERSION --build-arg PS_VERSION=$PS_VERSION --build-arg LEGACY_VERSION=$LATEST_MAJOR_TAG --build-arg BOXTAL_LOGIN=$BOXTAL_LOGIN --build-arg BOXTAL_PWD=$BOXTAL_PWD
