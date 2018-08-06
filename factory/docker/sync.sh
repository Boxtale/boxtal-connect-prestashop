#!/usr/bin/env bash

docker cp . boxtal_prestashop:/home/docker
docker exec -u root boxtal_prestashop chown -R docker:docker /home/docker
docker exec -u root boxtal_prestashop cp -R /var/www/html/modules/boxtal/lib/config.json /tmp
docker exec -u root boxtal_prestashop bash /home/docker/factory/common/sync.sh /home/docker