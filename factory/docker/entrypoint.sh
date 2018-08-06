#!/usr/bin/env bash

if ! [ -z "$APIURL" ]; then
    ESCAPED_APIURL=$(sed 's|/|\\/|g' <<< $APIURL)
    sudo sed -i "s/apiUrl\": \"https:\/\/api.boxtal.com\"/apiUrl\": \"$ESCAPED_APIURL\"/" /home/docker/vendor/boxtal/boxtal-php-poc/src/config.json
    sudo -u www-data -H sh -c "sed -i \"s/apiUrl\\\": \\\"https:\/\/api.boxtal.com\\\"/apiUrl\\\": \\\"$ESCAPED_APIURL\\\"/\"  /var/www/html/modules/boxtal/lib/config.json"
fi

sudo service mysql start
sudo a2enmod rewrite
sudo service apache2 start

if ! [ -z "$SITEURL" ]; then
    mysql -u dbadmin -pdbpass -e "UPDATE prestashop.ps_shop_url set domain=\"$SITEURL\";"
    mysql -u dbadmin -pdbpass -e "UPDATE prestashop.ps_shop_url set domain_ssl=\"$SITEURL\";"
fi

while true; do
	tail -f /dev/null & wait ${!}
done