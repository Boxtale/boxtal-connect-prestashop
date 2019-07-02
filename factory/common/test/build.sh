#!/usr/bin/env bash

PS_VERSION=${1-1.7.3.3}
MULTISTORE=${2-0}
TRAVIS=${3-false}
PS_DIR='/var/www/html'
UNIT_TESTS_DIR='/var/www/html/boxtal-unit-tests'

set -ex

clone_ps_repo() {
  if [[ -d $PS_REPO_DIR ]]; then
    sudo rm -rf $PS_REPO_DIR
  fi
  sudo mkdir -p $PS_REPO_DIR
  sudo chmod 777 $PS_REPO_DIR
  git clone https://github.com/PrestaShop/PrestaShop.git $PS_REPO_DIR
  cd $PS_REPO_DIR
  git checkout tags/$PS_VERSION
  cd $HOME
}

install_unit_tests() {
  sudo mkdir -p $PS_DIR/tests
  sudo cp -R $PS_REPO_DIR/tests/. $PS_DIR/tests
  if [[ -f $PS_REPO_DIR/composer.json ]]; then
    sudo cp -R $PS_REPO_DIR/composer.json $PS_DIR
    sudo rm -rf $PS_DIR/composer.lock
  else
    sudo rm -rf $PS_DIR/tests/composer.lock
  fi
  sudo chown -R www-data:www-data /var/www
  sudo find /var/www -type d -exec chmod 775 {} \;
  sudo find /var/www -type f -exec chmod 644 {} \;
  if [ ${TRAVIS} = "false" ]; then
    COMPOSER=$PS_DIR/composer.json
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php -r "if (hash_file('sha384', 'composer-setup.php') === '48e3236262b34d30969dca3c37281b3b4bbe3221bda826ac6a9a62d6444cdb0dcd0615698a5cbe587c3f0fe57a54d8f5') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
    php composer-setup.php
    php -r "unlink('composer-setup.php');"
    sudo mv composer.phar /usr/local/bin/composer
    if [[ -f $PS_REPO_DIR/composer.json ]]; then
      sudo -H -u www-data bash -c "composer clear-cache -d $PS_DIR"
      sudo -H -u www-data bash -c "composer update -d $PS_DIR --prefer-dist --no-interaction"
    else
      sudo -H -u www-data bash -c "composer clear-cache -d $PS_DIR/tests"
      sudo -H -u www-data bash -c "composer update -d $PS_DIR/tests --prefer-dist --no-interaction"
    fi
  fi

  # install plugin
  sudo wget https://github.com/nenes25/prestashop_console/raw/master/bin/prestashopConsole.phar -P $PS_DIR
  sudo chmod 777 $PS_DIR/prestashopConsole.phar
  sudo chown www-data:www-data $PS_DIR/prestashopConsole.phar
  cd $PS_DIR
  sudo ./prestashopConsole.phar module:install -vvv boxtalconnect
  cd $HOME

  # patch sandbox configuration
  $HOME/factory/common/test/patch-sandbox-configuration.sh $MULTISTORE

  # deactivate new order emails
  mysql -u dbadmin -pdbpass -D "prestashop" -e "UPDATE ps_configuration SET value=3 WHERE name='PS_MAIL_METHOD';"

  # add test database (used only in 1.7.x.x)
  mysqladmin -u dbadmin -pdbpass create test_prestashop
  mysqldump -u dbadmin -pdbpass prestashop | mysql -u dbadmin -pdbpass test_prestashop

  # patch sandbox configuration
  #$HOME/factory/common/test/patch-sandbox-configuration.sh $MULTISTORE 'test_'

  # copy helpers
  sudo rm -rf $PS_DIR/boxtal-unit-tests-helpers
  sudo mkdir -p $PS_DIR/boxtal-unit-tests-helpers
  sudo cp -R $HOME/test/unit-tests/helper/* $PS_DIR/boxtal-unit-tests-helpers

  # fix dev cache rights
  if [[ -d $PS_DIR/app/cache/dev ]]; then
    sudo chown -R www-data:www-data $PS_DIR/app/cache/dev
    sudo chmod -R 777 $PS_DIR/app/cache/dev
    sudo chmod -R 777 $PS_DIR/cache/smarty
  fi

  # fix smarty cache rights
  if [[ -d $PS_DIR/cache/smarty ]]; then
    sudo chown -R www-data:www-data $PS_DIR/cache/smarty
    sudo chmod -R 777 $PS_DIR/cache/smarty
  fi
}

if [ ${TRAVIS} = "false" ]; then
	HOME='/home/docker'
	PS_REPO_DIR=$HOME/ps
	clone_ps_repo
else
	HOME='/home/travis/build/Boxtale/boxtal-connect-prestashop'
	PS_REPO_DIR=$HOME/ps
  clone_ps_repo
fi

SOURCE_TEST_DIR=$HOME/test/unit-tests

install_unit_tests
