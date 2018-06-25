#!/usr/bin/env bash

PS_VERSION=${1-1.7.3.3}
TRAVIS=${2-false}
PS_DIR='/var/www/html'
UNIT_TESTS_DIR='/var/www/html/boxtal-unit-tests'

clone_ps_repo() {
  sudo mkdir -p $PS_REPO_DIR
  sudo chmod 777 $PS_REPO_DIR
  git clone https://github.com/PrestaShop/PrestaShop.git $PS_REPO_DIR
  cd $PS_REPO_DIR
  git checkout tags/$PS_VERSION
  cd $HOME
}

install_ps() {
  $HOME/build/install-ps.sh $PS_VERSION
}

install_unit_tests() {
  sudo mkdir -p $PS_DIR/tests
  sudo cp -R $PS_REPO_DIR/tests/. $PS_DIR/tests
  sudo cp -R $PS_REPO_DIR/composer.json $PS_DIR
  sudo chown -R www-data:www-data /var/www
  sudo find /var/www -type d -exec chmod 775 {} \;
  sudo find /var/www -type f -exec chmod 644 {} \;
  COMPOSER=$PS_DIR/composer.json
  # sudo -H -u www-data bash -c "composer install --prefer-dist --dev -d $PS_DIR"
  sudo -H -u www-data bash -c "composer require phpunit/phpunit:~6.2 --dev -d $PS_DIR -q"

  # add test database
  mysqladmin -u dbadmin -pdbpass create test_prestashop
  mysqldump  -u dbadmin -pdbpass prestashop | mysql -u dbadmin -pdbpass test_prestashop
}

if [ ${TRAVIS} = "false" ]; then
	HOME='/home/docker'
	PS_REPO_DIR=$HOME/ps
else
	HOME='/home/travis/build/Boxtale/boxtal-prestashop-poc'
	PS_REPO_DIR=$HOME/ps
  clone_ps_repo
	install_ps
fi

SOURCE_TEST_DIR=$HOME/test/unit-tests

install_unit_tests
