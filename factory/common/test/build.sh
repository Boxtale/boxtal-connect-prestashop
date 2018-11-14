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

install_ps() {
  $HOME/factory/common/install-ps.sh $PS_VERSION
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
  COMPOSER=$PS_DIR/composer.json
  sudo php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
  sudo php -r "if (hash_file('SHA384', 'composer-setup.php') === '93b54496392c062774670ac18b134c3b3a95e5a5e5c8f1a9f115f203b75bf9a129d5daa8ba6a13e2cc8a1da0806388a8') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
  sudo php composer-setup.php
  sudo php -r "unlink('composer-setup.php');"
  sudo mv composer.phar /usr/local/bin/composer
  if [[ -f $PS_REPO_DIR/composer.json ]]; then
    sudo -u www-data -H sh -c "sed -i \"s/https:\/\/github.com\/prestashop\/php-cssjanus/git@github.com:PrestaShop\/php-cssjanus/\" $PS_DIR/composer.json"
    ls -al /home/travis
    ls -al /home/travis/build
    echo -e "Host github.com\n\tStrictHostKeyChecking no\n" >> $HOME/.ssh/config
    sudo -H -u www-data bash -c "composer clear-cache -d $PS_DIR"
    sudo -H -u www-data bash -c "composer update -d $PS_DIR --prefer-dist --no-interaction"
  else
    sudo -H -u www-data bash -c "composer clear-cache -d $PS_DIR/tests"
    sudo -H -u www-data bash -c "composer update -d $PS_DIR/tests --prefer-dist --no-interaction"
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
  $HOME/factory/common/test/patch-sandbox-configuration.sh $MULTISTORE 'test_'

  # copy helpers
  sudo rm -rf $PS_DIR/boxtal-unit-tests-helpers
  sudo mkdir -p $PS_DIR/boxtal-unit-tests-helpers
  sudo cp -R $HOME/test/unit-tests/helper/* $PS_DIR/boxtal-unit-tests-helpers

  # fix dev cache rights
  if [[ -d $PS_DIR/app/cache/dev ]]; then
    sudo chown -R www-data:www-data $PS_DIR/app/cache/dev
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
	install_ps
fi

SOURCE_TEST_DIR=$HOME/test/unit-tests

install_unit_tests
