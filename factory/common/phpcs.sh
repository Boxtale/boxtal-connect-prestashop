#!/usr/bin/env bash

TRAVIS=${1-false}

if [ ${TRAVIS} = "false" ]; then
    vendor/bin/phpcbf -p .
fi

vendor/bin/phpcs -s -p .
