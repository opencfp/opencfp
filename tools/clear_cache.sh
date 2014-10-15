#!/usr/bin/env bash

# Get a path to the project's root.
PROJECT_ROOT=$(/usr/local/bin/git rev-parse --show-toplevel)

# Destroy cache.
rm -rf $PROJECT_ROOT/cache/*