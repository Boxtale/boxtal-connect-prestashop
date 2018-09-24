#!/usr/bin/env bash

docker cp . boxtal_prestashop:/home/docker
docker exec -u root boxtal_prestashop chown -R docker:docker /home/docker
docker exec -u root boxtal_prestashop bash /home/docker/factory/common/sync.sh /home/docker
docker exec -u root boxtal_prestashop bash /home/docker/factory/common/sync-properties.sh /home/docker
