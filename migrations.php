<?php

return [
    'name' => 'OpenCFP Migrations',
    'migrations_namespace' => 'OpenCFP\Migrations',
    'table_name' => 'migration_versions',
    'column_name' => 'version',
    'column_length' => 14,
    'executed_at_column_name' => 'executed_at',
    'migrations_directory' => '/migrations',
    'all_or_nothing' => true,
    'check_database_platform' => true,
];
