#!/usr/bin/env bash

HOME=${1-/home/docker}

sudo chmod -R +x $HOME/test/bin
sudo chown -R www-data:www-data /var/www/html
gulp css
gulp js
sudo mkdir -p src/lib
sudo cp -R vendor/boxtal/boxtal-php-poc/src/* src/lib
sudo -H -u www-data bash -c "rm -rf /var/www/html/modules/boxtal"
sudo -H -u www-data bash -c "mkdir -p /var/www/html/modules/boxtal"
sudo -H -u www-data bash -c "cp -R src/* /var/www/html/modules/boxtal"
sudo -H -u www-data bash -c "rm -rf /var/www/html/boxtal-unit-tests"
sudo -H -u www-data bash -c "mkdir -p /var/www/html/boxtal-unit-tests"
sudo -H -u www-data bash -c "cp -R test/unit-tests/Test*.php /var/www/html/boxtal-unit-tests"
sudo -H -u www-data bash -c "chmod -R 775 /var/www/html"
sudo -H -u www-data bash -c "cp -R test/unit-tests/phpunit.xml /var/www/html"
sudo -H -u www-data bash -c "cp -R test/unit-tests/bootstrap.php /var/www/html"
sudo -H -u www-data bash -c "chown -R www-data:www-data /var/www/html/modules/boxtal"
sudo -H -u www-data bash -c "find /var/www/html/modules/boxtal -type d -exec chmod 775 {} \;"
sudo -H -u www-data bash -c "find /var/www/html/modules/boxtal -type f -exec chmod 644 {} \;"
