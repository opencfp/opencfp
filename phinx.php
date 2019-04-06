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

use OpenCFP\Environment;
use OpenCFP\Kernel;

require_once __DIR__ . '/vendor/autoload.php';

$kernel = new Kernel((string) Environment::fromServer(\getenv()), false);
$kernel->boot();

$container = $kernel->getContainer();

return [
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/migrations',
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_database'        => $kernel->getEnvironment(),
        $kernel->getEnvironment() => [
            'adapter'   => 'mysql',
            'host'      => $container->getParameter('database.host'),
            'name'      => $container->getParameter('database.database'),
            'user'      => $container->getParameter('database.user'),
            'pass'      => $container->getParameter('database.password'),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ],
    ],
];
