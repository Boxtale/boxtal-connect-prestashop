#!/usr/bin/env bash

sudo service mysql start
sudo a2enmod rewrite
sudo service apache2 start

if ! [ -z "$APIURL" ]; then
    mysql -u dbadmin -pdbpass -e "UPDATE prestashop.ps_shop_url set domain=\"$APIURL\";"
    mysql -u dbadmin -pdbpass -e "UPDATE prestashop.ps_shop_url set domain_ssl=\"$APIURL\";"
fi

while true; do
	tail -f /dev/null & wait ${!}
done
