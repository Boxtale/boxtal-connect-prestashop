#!/usr/bin/env bash

echo "starting prestashop install"
set -ex

PS_VERSION=${1-latest}
MULTISTORE=${2-0}

TMPSITETITLE="Prestatest"
TMPSITEADMINLOGIN="admin"
TMPSITEADMINPWD="admin"
TMPSITEADMINEMAIL="test_prestashop@boxtal.com"
PS_CORE_DIR=/var/www/html

download() {
  if [ `which curl` ]; then
    curl -s "$1" > "$2";
  elif [ `which wget` ]; then
    wget -nv -O "$2" "$1"
  fi
}

clean_ps_dir() {
  sudo rm $PS_CORE_DIR/index.html
}

install_ps() {
  mkdir -p /tmp/prestashop
  download https://download.prestashop.com/download/releases/prestashop_${PS_VERSION}.zip  /tmp/prestashop.zip
  unzip -q /tmp/prestashop.zip -d /tmp/prestashop/
  mkdir -p /tmp/prestashop/src
  if [ -f "/tmp/prestashop/prestashop.zip" ]; then
    unzip -q /tmp/prestashop/prestashop.zip -d /tmp/prestashop/src
  else
    cp -R /tmp/prestashop/prestashop/* /tmp/prestashop/src
  fi
  sudo mv /tmp/prestashop/src/* $PS_CORE_DIR
  mysqladmin -u dbadmin -pdbpass create prestashop
  sudo rm -rf $PS_CORE_DIR/app/cache/*
  php $PS_CORE_DIR/install/index_cli.php --domain="localhost" --db_name=prestashop --db_user=dbadmin --db_password=dbpass --name="$TMPSITETITLE" --email="admin@boxtal.com" --password=admin
  sudo rm -rf $PS_CORE_DIR/install
  sudo mv $PS_CORE_DIR/admin $PS_CORE_DIR/adminboxtal
  sed -i "s/define('_PS_MODE_DEV_', false)/define('_PS_MODE_DEV_', true)/" $PS_CORE_DIR/config/defines.inc.php
  mysql -u dbadmin -pdbpass -D "prestashop" -e "UPDATE ps_configuration SET value=0 WHERE name='PS_SMARTY_CACHE';"
  mysql -u dbadmin -pdbpass -D "prestashop" -e "UPDATE ps_configuration SET value=1 WHERE name='PS_SMARTY_FORCE_COMPILE';"

  if [[ $MULTISTORE = "1" ]]; then
    install_multistore
  fi
}

install_multistore() {
  mysql -u dbadmin -pdbpass -D "prestashop" < factory/common/multistore/$PS_VERSION
  mysql -u dbadmin -pdbpass -D "prestashop" < factory/common/multistore/$PS_VERSION-virtual
  sudo cp factory/common/multistore/$PS_VERSION-virtual-htaccess $PS_CORE_DIR/.htaccess
}

clean_ps_dir
install_ps
