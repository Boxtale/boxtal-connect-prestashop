#!/usr/bin/env bash

# Remove Xdebug from PHP runtime for all PHP version except 7.2 to speed up builds.
# We need Xdebug enabled in the PHP 7.2 build job as it is used to generate code coverage.
if [[ ${RUN_CODE_COVERAGE} != 1 ]]; then
    phpenv config-rm xdebug.ini
fi
