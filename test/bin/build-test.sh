#!/usr/bin/env bash

PS_VERSION=${1-1.7.3.3}
TRAVIS=${2-false}
PS_DIR='/var/www/html'
UNIT_TESTS_DIR='/var/www/html/boxtal-unit-tests'
PS_REPO_DIR='/tmp/ps'

install_ps() {
  $HOME/build/install-ps.sh $PS_VERSION
}

install_unit_tests() {
  # init ps repo
  sudo mkdir -p $PS_REPO_DIR
  sudo chmod -R 777 $PS_REPO_DIR
  git clone https://github.com/PrestaShop/PrestaShop.git $PS_REPO_DIR
  cd $PS_REPO_DIR
  git checkout tags/$PS_VERSION
  cd $HOME
  sudo mkdir -p $PS_DIR/tests
  ls -l $PS_REPO_DIR
  ls -l $PS_REPO_DIR/PrestaShop
  sudo cp -R $PS_REPO_DIR/PrestaShop/tests/. $PS_DIR/tests
  sudo cp -R $PS_REPO_DIR/PrestaShop/composer.json $PS_DIR
  sudo composer install --prefer-dist -d $PS_DIR

  # add test database
  mysqladmin -u dbadmin -pdbpass create test_prestashop
  mysqldump  -u dbadmin -pdbpass prestashop | mysql -u dbadmin -pdbpass test_prestashop
}

copy_unit_tests() {
  sudo cp -R $HOME/test/unit-tests/phpunit.xml $PS_DIR
  sudo cp -R $HOME/test/unit-tests/bootstrap.php $PS_DIR

  sudo mkdir -p $UNIT_TESTS_DIR
  sudo cp -R $HOME/test/unit-tests/test-*.php $UNIT_TESTS_DIR
}


if [ ${TRAVIS} = "false" ]; then
	HOME='/home/docker'
else
	HOME='/home/travis/build/Boxtale/boxtal-prestashop-poc'
	install_ps
fi

SOURCE_TEST_DIR=$HOME/test/unit-tests

install_unit_tests
copy_unit_tests
