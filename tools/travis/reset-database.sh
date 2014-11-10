#!/usr/bin/env bash

# Note that this is also specified in the Phinx testing environment.
TRAVIS_DB="cfp_travis"

# Get a path to the project's root.
PROJECT_ROOT=$(git rev-parse --show-toplevel)

# Set up testing database.
echo "Setting up testing database..."
mysql -e "DROP DATABASE IF EXISTS $TRAVIS_DB" -uroot
mysql -e "CREATE DATABASE $TRAVIS_DB" -uroot

# Run Phinx migrations.
echo "Running migrations..."
$PROJECT_ROOT/vendor/bin/phinx --configuration="$PROJECT_ROOT/phinx.yml" migrate -e testing