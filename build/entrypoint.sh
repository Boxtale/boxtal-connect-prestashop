#!/usr/bin/env bash

sudo service mysql start
sudo a2enmod rewrite
sudo service apache2 start

while true; do
	tail -f /dev/null & wait ${!}
done
