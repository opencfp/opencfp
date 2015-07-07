#!/usr/bin/env bash

# Get a path to the project's root.
PROJECT_ROOT=$(git rev-parse --show-toplevel)

# Set up testing database.
echo "Setting up testing database..."
mysql -e "DROP DATABASE IF EXISTS $TRAVIS_DB" -uroot
mysql -e "CREATE DATABASE $TRAVIS_DB" -uroot

# Run Phinx migrations.
echo "Running migrations..."
$PROJECT_ROOT/vendor/bin/phinx --configuration="$PROJECT_ROOT/phinx.yml" migrate -e testing
