#!/usr/bin/env bash

docker cp . boxtal_prestashop:/home/docker
docker exec -u root boxtal_prestashop chown -R docker:docker /home/docker
docker exec -u root boxtal_prestashop chmod -R +x /home/docker/test/bin
docker exec -u root boxtal_prestashop chown -R www-data:www-data /var/www/html
docker exec -u root boxtal_prestashop gulp css
docker exec -u root boxtal_prestashop gulp js
docker exec -u root boxtal_prestashop mkdir -p src/lib
docker exec -u root boxtal_prestashop cp -R vendor/boxtal/boxtal-php-poc/src/* src/lib
docker exec -u www-data boxtal_prestashop rm -rf /var/www/html/modules/boxtal
docker exec -u www-data boxtal_prestashop mkdir -p /var/www/html/modules/boxtal
docker exec -u www-data boxtal_prestashop cp -R src/* /var/www/html/modules/boxtal
docker exec -u www-data boxtal_prestashop rm -rf /var/www/html/boxtal-unit-tests
docker exec -u www-data boxtal_prestashop mkdir -p /var/www/html/boxtal-unit-tests
docker exec -u www-data boxtal_prestashop cp -R test/unit-tests/test-*.php /var/www/html/boxtal-unit-tests
docker exec -u www-data boxtal_prestashop chmod -R 775 /var/www/html
docker exec -u www-data boxtal_prestashop cp -R test/unit-tests/phpunit.xml /var/www/html
docker exec -u www-data boxtal_prestashop cp -R test/unit-tests/bootstrap.php /var/www/html
docker exec -u www-data boxtal_prestashop chown -R www-data:www-data /var/www/html/modules/boxtal

docker exec -u www-data boxtal_prestashop find /var/www/html/modules/boxtal -type d -exec chmod 775 {} \;
docker exec -u www-data boxtal_prestashop find /var/www/html/modules/boxtal -type f -exec chmod 644 {} \;
