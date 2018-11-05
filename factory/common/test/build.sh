#!/usr/bin/env bash

PS_VERSION=${1-1.7.3.3}
TRAVIS=${2-false}
PS_DIR='/var/www/html'
UNIT_TESTS_DIR='/var/www/html/boxtal-unit-tests'

set -ex

clone_ps_repo() {
  sudo mkdir -p $PS_REPO_DIR
  sudo chmod 777 $PS_REPO_DIR
  git clone https://github.com/PrestaShop/PrestaShop.git $PS_REPO_DIR
  cd $PS_REPO_DIR
  git checkout tags/$PS_VERSION
  cd $HOME
}

install_ps() {
  $HOME/factory/common/install-ps.sh $PS_VERSION
}

install_unit_tests() {
  sudo mkdir -p $PS_DIR/tests
  sudo cp -R $PS_REPO_DIR/tests/. $PS_DIR/tests
  sudo cp -R $PS_REPO_DIR/composer.json $PS_DIR
  sudo chown -R www-data:www-data /var/www
  sudo find /var/www -type d -exec chmod 775 {} \;
  sudo find /var/www -type f -exec chmod 644 {} \;
  COMPOSER=$PS_DIR/composer.json
  php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
  php -r "if (hash_file('SHA384', 'composer-setup.php') === '93b54496392c062774670ac18b134c3b3a95e5a5e5c8f1a9f115f203b75bf9a129d5daa8ba6a13e2cc8a1da0806388a8') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
  php composer-setup.php
  php -r "unlink('composer-setup.php');"
  sudo mv composer.phar /usr/local/bin/composer
  #sudo rm -rf $PS_DIR/vendor
  sudo rm -rf $PS_DIR/composer.lock
  sudo -H -u www-data bash -c "composer clear-cache -d $PS_DIR"
  sudo -H -u www-data bash -c "composer update -d $PS_DIR --prefer-dist --no-interaction"

  # install plugin
  sudo wget https://github.com/nenes25/prestashop_console/raw/master/bin/prestashopConsole.phar -P $PS_DIR
  sudo chmod +x $PS_DIR/prestashopConsole.phar
  cd $PS_DIR
  sudo ./prestashopConsole.phar module:install boxtalconnect
  cd $HOME

  # add test database0
  mysqladmin -u dbadmin -pdbpass create test_prestashop
  mysqldump  -u dbadmin -pdbpass prestashop | mysql -u dbadmin -pdbpass test_prestashop
}

if [ ${TRAVIS} = "false" ]; then
	HOME='/home/docker'
	PS_REPO_DIR=$HOME/ps
	clone_ps_repo
else
	HOME='/home/travis/build/Boxtale/boxtal-connect-prestashop'
	PS_REPO_DIR=$HOME/ps
  clone_ps_repo
	install_ps
fi

SOURCE_TEST_DIR=$HOME/test/unit-tests

install_unit_tests
