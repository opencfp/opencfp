<?php

use OpenCFP\Kernel;

require_once __DIR__ . '/vendor/autoload.php';

$kernel = new Kernel(\OpenCFP\Environment::fromServer($_SERVER), false);
$kernel->boot();

$container = $kernel->getContainer();

return [
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/migrations',
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_database' => $kernel->getEnvironment(),
        $kernel->getEnvironment() => [
            'adapter' => 'mysql',
            'host' => $container->getParameter('database.host'),
            'name' => $container->getParameter('database.database'),
            'user' => $container->getParameter('database.user'),
            'pass' => $container->getParameter('database.password'),
        ],
    ]
];
