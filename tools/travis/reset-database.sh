#!/usr/bin/env bash

# Get a path to the project's root.
PROJECT_ROOT=$(git rev-parse --show-toplevel)

# Run Phinx migrations.
echo "Running migrations..."
$PROJECT_ROOT/vendor/bin/phinx --configuration="$PROJECT_ROOT/phinx.yml" migrate -e testing
