<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2019 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

return [
    'name'                    => 'OpenCFP Migrations',
    'migrations_namespace'    => 'OpenCFP\Migrations',
    'table_name'              => 'migration_versions',
    'column_name'             => 'version',
    'column_length'           => 14,
    'executed_at_column_name' => 'executed_at',
    'migrations_directory'    => '/migrations',
    'all_or_nothing'          => true,
    'check_database_platform' => true,
];
