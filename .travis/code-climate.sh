#!/usr/bin/env bash

# See
#
# - https://github.com/codeclimate/test-reporter#installation--usage
# - https://docs.travis-ci.com/user/environment-variables/#Default-Environment-Variables

function code-climate-before-script()
{
    curl -L https://codeclimate.com/downloads/test-reporter/test-reporter-latest-linux-amd64 > ./cc-test-reporter
    chmod +x ./cc-test-reporter
    ./cc-test-reporter before-build
}

function code-climate-after-script()
{
    ./cc-test-reporter after-build --exit-code $TRAVIS_TEST_RESULT
}
