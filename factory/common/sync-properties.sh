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
  if [[ $PS_SITEURL =~ "https" ]]; then
    mysql -u dbadmin -pdbpass -e "UPDATE prestashop.ps_configuration SET value=1 WHERE name=\"PS_SSL_ENABLED\";"
    mysql -u dbadmin -pdbpass -e "INSERT IGNORE INTO prestashop.ps_configuration (name, value) VALUES ('PS_SSL_ENABLED_EVERYWHERE', 1);"
    DOMAIN=`echo $PS_SITEURL | cut -c 9-`
  else
    DOMAIN=`echo $PS_SITEURL | cut -c 8-`
  fi

    mysql -u dbadmin -pdbpass -e "UPDATE prestashop.ps_shop_url set domain=\"$DOMAIN\";"
    mysql -u dbadmin -pdbpass -e "UPDATE prestashop.ps_shop_url set domain_ssl=\"$DOMAIN\";"
fi
