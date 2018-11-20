#!/usr/bin/env bash

set -ex

RUN_CODE_COVERAGE=${1-0}

if [[ ${RUN_CODE_COVERAGE} != 1 ]]; then
    phpenv config-rm xdebug.ini
fi
