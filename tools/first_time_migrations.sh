#!/usr/bin/env bash

# Get a path to the project's root.
PROJECT_ROOT=$(git rev-parse --show-toplevel)

# Do MySQL base schema import.
mysql -uroot cfp < "$PROJECT_ROOT/migrations/schema.sql"

# Run Phinx migrations.
$PROJECT_ROOT/vendor/bin/phinx --configuration="$PROJECT_ROOT/phinx.yml" migrate