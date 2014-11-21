#!/usr/bin/env bash

# Get a path to the project's root.
PROJECT_ROOT=$(git rev-parse --show-toplevel)

sh $PROJECT_ROOT/tools/travis/reset-database.sh

phpunit -c "$PROJECT_ROOT/phpunit.xml" --coverage-clover build/logs/clover.xml && $PROJECT_ROOT/vendor/bin/behat --colors -vvv
