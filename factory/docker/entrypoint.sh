#!/usr/bin/env bash

set -ex

sudo service mysql start
sudo a2enmod rewrite
sudo service apache2 start

touch properties
if ! [ -z "$APIURL" ]; then
    echo "APIURL=$APIURL" >> properties
fi
if ! [ -z "$ONBOARDINGURL" ]; then
    echo "ONBOARDINGURL=$ONBOARDINGURL" >> properties
fi
if ! [ -z "$PS_SITEURL" ]; then
    echo "PS_SITEURL=$PS_SITEURL" >> properties
fi

./factory/common/sync-properties.sh

while true; do
	tail -f /dev/null & wait ${!}
done
