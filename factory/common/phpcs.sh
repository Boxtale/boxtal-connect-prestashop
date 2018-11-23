#!/usr/bin/env bash

TRAVIS=${1-false}

if [[ ${TRAVIS} = "false" ]]; then
    vendor/bin/php-cs-fixer fix --config .php_cs.dist
fi

vendor/bin/php-cs-fixer fix --config .php_cs.dist -v --dry-run --stop-on-violation --using-cache=no
