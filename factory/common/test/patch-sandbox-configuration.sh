#!/usr/bin/env bash

echo "patching sandbox configuration"
set -ex

MULTISTORE=${1-0}
DB_PREFIX=${2-}

PS_DIR='/var/www/html'
ACCESS_KEY='5V6XM404FA6FD20T2PYS'
SECRET_KEY='b06a233a-1baf-473a-a698-6855228cb4ef'
SANDBOX_MAP_BOOTSTRAP_URL='https://maps.boxtal.build/styles/boxtal/style.json?access_token=${access_token}'
SANDBOX_MAP_TOKEN_URL='https://api.boxtal.build/v2/token/maps'
SANDBOX_MAP_LOGO_IMAGE_URL='https://resource.boxtal.com/images/boxtal-maps.svg'
SANDBOX_MAP_LOGO_HREF_URL='https://www.boxtal.com'
PP_NETWORKS='a:4:{s:12:"CHRP_NETWORK";a:1:{i:0;s:10:"Chronopost";}s:12:"SOGP_NETWORK";a:1:{i:0;s:12:"Relais colis";}s:12:"UPSE_NETWORK";a:1:{i:0;s:3:"UPS";}s:12:"MONR_NETWORK";a:4:{i:0;s:13:"Mondial Relay";i:1;s:10:"Happy Post";i:2;s:10:"Punto Pack";i:3;s:20:"Boxtal Mondial Relay";}}'
SANDBOX_TRACKING_URL_PATTERN='https://www.boxtal.org/tracking/5V6XM404FA6FD20T2PYS?orderReference=%s'

SANDBOX_API_URL='https://api.boxtal.build'
ESCAPED_APIURL=$(sed 's|/|\\/|g' <<< $SANDBOX_API_URL)
sudo -u www-data -H sh -c "sed -i \"s/apiUrl\\\": \\\"https:\/\/api.boxtal.org\\\"/apiUrl\\\": \\\"$ESCAPED_APIURL\\\"/\"  $PS_DIR/modules/boxtalconnect/lib/config.json"
sudo -u www-data -H sh -c "sed -i \"s/apiUrl\\\": \\\"https:\/\/api.boxtal.com\\\"/apiUrl\\\": \\\"$ESCAPED_APIURL\\\"/\"  $PS_DIR/modules/boxtalconnect/lib/config.json"

SANDBOX_ONBOARDING_URL='https://www.boxtal.build/onboarding'
ESCAPED_ONBOARDINGURL=$(sed 's|/|\\/|g' <<< $SANDBOX_ONBOARDING_URL)
sudo -u www-data -H sh -c "sed -i \"s/https:\/\/www.boxtal.org\/onboarding/$ESCAPED_ONBOARDINGURL/\" $PS_DIR/modules/boxtalconnect/boxtalconnect.php"
sudo -u www-data -H sh -c "sed -i \"s/https:\/\/www.boxtal.com\/onboarding/$ESCAPED_ONBOARDINGURL/\" $PS_DIR/modules/boxtalconnect/boxtalconnect.php"

if [[ $MULTISTORE = "1" ]]; then
    mysql -u dbadmin -pdbpass -D $DB_PREFIX"prestashop" -e "INSERT INTO ps_configuration (id_shop_group, id_shop, name, value) VALUES (1, 1, 'BX_ACCESS_KEY', '$ACCESS_KEY') ON DUPLICATE KEY UPDATE value='$ACCESS_KEY';"
    mysql -u dbadmin -pdbpass -D $DB_PREFIX"prestashop" -e "INSERT INTO ps_configuration (id_shop_group, id_shop, name, value) VALUES (1, 1, 'BX_SECRET_KEY', '$SECRET_KEY') ON DUPLICATE KEY UPDATE value='$SECRET_KEY';"
    mysql -u dbadmin -pdbpass -D $DB_PREFIX"prestashop" -e "INSERT INTO ps_configuration (id_shop_group, id_shop, name, value) VALUES (1, 1, 'BX_MAP_BOOTSTRAP_URL', '$SANDBOX_MAP_BOOTSTRAP_URL') ON DUPLICATE KEY UPDATE value='$SANDBOX_MAP_BOOTSTRAP_URL';"
    mysql -u dbadmin -pdbpass -D $DB_PREFIX"prestashop" -e "INSERT INTO ps_configuration (id_shop_group, id_shop, name, value) VALUES (1, 1, 'BX_MAP_TOKEN_URL', '$SANDBOX_MAP_TOKEN_URL') ON DUPLICATE KEY UPDATE value='$SANDBOX_MAP_TOKEN_URL';"
    mysql -u dbadmin -pdbpass -D $DB_PREFIX"prestashop" -e "INSERT INTO ps_configuration (id_shop_group, id_shop, name, value) VALUES (1, 1, 'BX_MAP_LOGO_IMAGE_URL', '$SANDBOX_MAP_LOGO_IMAGE_URL') ON DUPLICATE KEY UPDATE value='$SANDBOX_MAP_LOGO_IMAGE_URL';"
    mysql -u dbadmin -pdbpass -D $DB_PREFIX"prestashop" -e "INSERT INTO ps_configuration (id_shop_group, id_shop, name, value) VALUES (1, 1, 'BX_MAP_LOGO_HREF_URL', '$SANDBOX_MAP_LOGO_HREF_URL') ON DUPLICATE KEY UPDATE value='$SANDBOX_MAP_LOGO_HREF_URL';"
    mysql -u dbadmin -pdbpass -D $DB_PREFIX"prestashop" -e "INSERT INTO ps_configuration (id_shop_group, id_shop, name, value) VALUES (1, 1, 'BX_PP_NETWORKS', '$PP_NETWORKS') ON DUPLICATE KEY UPDATE value='$PP_NETWORKS';"
    mysql -u dbadmin -pdbpass -D $DB_PREFIX"prestashop" -e "INSERT INTO ps_configuration (id_shop_group, id_shop, name, value) VALUES (1, 1, 'BX_TRACKING_URL_PATTERN', '$SANDBOX_TRACKING_URL_PATTERN') ON DUPLICATE KEY UPDATE value='$SANDBOX_TRACKING_URL_PATTERN';"

    mysql -u dbadmin -pdbpass -D $DB_PREFIX"prestashop" -e "INSERT INTO ps_configuration (id_shop_group, id_shop, name, value) VALUES (2, 2, 'BX_ACCESS_KEY', '$ACCESS_KEY') ON DUPLICATE KEY UPDATE value='$ACCESS_KEY';"
    mysql -u dbadmin -pdbpass -D $DB_PREFIX"prestashop" -e "INSERT INTO ps_configuration (id_shop_group, id_shop, name, value) VALUES (2, 2, 'BX_SECRET_KEY', '$SECRET_KEY') ON DUPLICATE KEY UPDATE value='$SECRET_KEY';"
    mysql -u dbadmin -pdbpass -D $DB_PREFIX"prestashop" -e "INSERT INTO ps_configuration (id_shop_group, id_shop, name, value) VALUES (2, 2, 'BX_MAP_BOOTSTRAP_URL', '$SANDBOX_MAP_BOOTSTRAP_URL') ON DUPLICATE KEY UPDATE value='$SANDBOX_MAP_BOOTSTRAP_URL';"
    mysql -u dbadmin -pdbpass -D $DB_PREFIX"prestashop" -e "INSERT INTO ps_configuration (id_shop_group, id_shop, name, value) VALUES (2, 2, 'BX_MAP_TOKEN_URL', '$SANDBOX_MAP_TOKEN_URL') ON DUPLICATE KEY UPDATE value='$SANDBOX_MAP_TOKEN_URL';"
    mysql -u dbadmin -pdbpass -D $DB_PREFIX"prestashop" -e "INSERT INTO ps_configuration (id_shop_group, id_shop, name, value) VALUES (2, 2, 'BX_MAP_LOGO_IMAGE_URL', '$SANDBOX_MAP_LOGO_IMAGE_URL') ON DUPLICATE KEY UPDATE value='$SANDBOX_MAP_LOGO_IMAGE_URL';"
    mysql -u dbadmin -pdbpass -D $DB_PREFIX"prestashop" -e "INSERT INTO ps_configuration (id_shop_group, id_shop, name, value) VALUES (2, 2, 'BX_MAP_LOGO_HREF_URL', '$SANDBOX_MAP_LOGO_HREF_URL') ON DUPLICATE KEY UPDATE value='$SANDBOX_MAP_LOGO_HREF_URL';"
    mysql -u dbadmin -pdbpass -D $DB_PREFIX"prestashop" -e "INSERT INTO ps_configuration (id_shop_group, id_shop, name, value) VALUES (2, 2, 'BX_PP_NETWORKS', '$PP_NETWORKS') ON DUPLICATE KEY UPDATE value='$PP_NETWORKS';"
    mysql -u dbadmin -pdbpass -D $DB_PREFIX"prestashop" -e "INSERT INTO ps_configuration (id_shop_group, id_shop, name, value) VALUES (2, 2, 'BX_TRACKING_URL_PATTERN', '$SANDBOX_TRACKING_URL_PATTERN') ON DUPLICATE KEY UPDATE value='$SANDBOX_TRACKING_URL_PATTERN';"
else
    mysql -u dbadmin -pdbpass -D $DB_PREFIX"prestashop" -e "INSERT INTO ps_configuration (id_shop_group, id_shop, name, value) VALUES (null, null, 'BX_ACCESS_KEY', '$ACCESS_KEY') ON DUPLICATE KEY UPDATE value='$ACCESS_KEY';"
    mysql -u dbadmin -pdbpass -D $DB_PREFIX"prestashop" -e "INSERT INTO ps_configuration (id_shop_group, id_shop, name, value) VALUES (null, null, 'BX_SECRET_KEY', '$SECRET_KEY') ON DUPLICATE KEY UPDATE value='$SECRET_KEY';"
    mysql -u dbadmin -pdbpass -D $DB_PREFIX"prestashop" -e "INSERT INTO ps_configuration (id_shop_group, id_shop, name, value) VALUES (null, null, 'BX_MAP_BOOTSTRAP_URL', '$SANDBOX_MAP_BOOTSTRAP_URL') ON DUPLICATE KEY UPDATE value='$SANDBOX_MAP_BOOTSTRAP_URL';"
    mysql -u dbadmin -pdbpass -D $DB_PREFIX"prestashop" -e "INSERT INTO ps_configuration (id_shop_group, id_shop, name, value) VALUES (null, null, 'BX_MAP_TOKEN_URL', '$SANDBOX_MAP_TOKEN_URL') ON DUPLICATE KEY UPDATE value='$SANDBOX_MAP_TOKEN_URL';"
    mysql -u dbadmin -pdbpass -D $DB_PREFIX"prestashop" -e "INSERT INTO ps_configuration (id_shop_group, id_shop, name, value) VALUES (null, null, 'BX_MAP_LOGO_IMAGE_URL', '$SANDBOX_MAP_LOGO_IMAGE_URL') ON DUPLICATE KEY UPDATE value='$SANDBOX_MAP_LOGO_IMAGE_URL';"
    mysql -u dbadmin -pdbpass -D $DB_PREFIX"prestashop" -e "INSERT INTO ps_configuration (id_shop_group, id_shop, name, value) VALUES (null, null, 'BX_MAP_LOGO_HREF_URL', '$SANDBOX_MAP_LOGO_HREF_URL') ON DUPLICATE KEY UPDATE value='$SANDBOX_MAP_LOGO_HREF_URL';"
    mysql -u dbadmin -pdbpass -D $DB_PREFIX"prestashop" -e "INSERT INTO ps_configuration (id_shop_group, id_shop, name, value) VALUES (null, null, 'BX_PP_NETWORKS', '$PP_NETWORKS') ON DUPLICATE KEY UPDATE value='$PP_NETWORKS';"
    mysql -u dbadmin -pdbpass -D $DB_PREFIX"prestashop" -e "INSERT INTO ps_configuration (id_shop_group, id_shop, name, value) VALUES (null, null, 'BX_TRACKING_URL_PATTERN', '$SANDBOX_TRACKING_URL_PATTERN') ON DUPLICATE KEY UPDATE value='$SANDBOX_TRACKING_URL_PATTERN';"
fi
