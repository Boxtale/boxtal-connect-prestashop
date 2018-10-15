#!/usr/bin/env bash

HOME=${1-/home/docker}
PS_CORE_DIR=/var/www/html

sh $HOME/properties

if ! [ -z "$APIURL" ]; then
    ESCAPED_APIURL=$(sed 's|/|\\/|g' <<< $APIURL)
    sudo -u www-data -H sh -c "sed -i \"s/apiUrl\\\": \\\"https:\/\/api.boxtal.com\\\"/apiUrl\\\": \\\"$ESCAPED_APIURL\\\"/\"  $PS_CORE_DIR/modules/boxtalconnect/lib/config.json"
fi

if ! [ -z "$ONBOARDINGURL" ]; then
    ESCAPED_ONBOARDINGURL=$(sed 's|/|\\/|g' <<< $ONBOARDINGURL)
    sudo -u www-data -H sh -c "sed -i \"s/https:\/\/www.boxtal.com\/onboarding/$ESCAPED_ONBOARDINGURL/\" $PS_CORE_DIR/modules/boxtalconnect/boxtalconnect.php"
fi

if ! [ -z "$PS_SITEURL" ]; then
    mysql -u dbadmin -pdbpass -e "UPDATE prestashop.ps_shop_url set domain=\"$PS_SITEURL\";"
    mysql -u dbadmin -pdbpass -e "UPDATE prestashop.ps_shop_url set domain_ssl=\"$PS_SITEURL\";"
fi
