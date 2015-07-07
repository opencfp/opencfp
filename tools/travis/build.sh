#!/usr/bin/env bash

# Get a path to the project's root.
PROJECT_ROOT=$(git rev-parse --show-toplevel)

phpunit --coverage-clover build/logs/clover.xml
