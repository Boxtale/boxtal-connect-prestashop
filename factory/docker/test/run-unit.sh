#!/usr/bin/env bash

docker exec -u www-data boxtal_prestashop /var/www/html/vendor/phpunit/phpunit/phpunit -c /var/www/html/phpunit.xml

