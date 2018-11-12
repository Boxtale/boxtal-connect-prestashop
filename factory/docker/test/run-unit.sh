#!/usr/bin/env bash

PHPUNIT_CHECK=$(docker exec -u www-data boxtal_connect_prestashop sh -c "[ -f /var/www/html/tests/vendor/phpunit/phpunit/phpunit ] && echo '1'")

if [ ${PHPUNIT_CHECK} = "1" ]; then
  PHPUNIT=/var/www/html/tests/vendor/phpunit/phpunit/phpunit
else
  PHPUNIT=/var/www/html/vendor/phpunit/phpunit/phpunit
fi

docker exec -u www-data boxtal_connect_prestashop $PHPUNIT -c /var/www/html/phpunit.xml
