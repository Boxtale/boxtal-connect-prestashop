#!/usr/bin/env bash

echo "starting prestashop install"
set -ex

PS_VERSION=${1-latest}
PORT=${2-80}

if [ $PORT = "80" ]; then
  TMPSITEURL="localhost"
else
  TMPSITEURL="localhost:$PORT"
fi

TMPSITETITLE="Boxtal Prestashop test site"
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
  unzip -q /tmp/prestashop/prestashop.zip -d /tmp/prestashop/src
  mv /tmp/prestashop/src/* $PS_CORE_DIR
  mysqladmin -u dbadmin -pdbpass create prestashop
  php $PS_CORE_DIR/install/index_cli.php --domain=$TMPSITEURL --db_name=prestashop --db_user=dbadmin --db_password=dbpass --name="$TMPSITETITLE" --email="admin@boxtal.com" --password=admin
  rm -rf $PS_CORE_DIR/install
  mv $PS_CORE_DIR/admin $PS_CORE_DIR/adminboxtal
  sed -i "s/define('_PS_MODE_DEV_', false)/define('_PS_MODE_DEV_', true)/" $PS_CORE_DIR/config/defines.inc.php
}

clean_ps_dir
install_ps
