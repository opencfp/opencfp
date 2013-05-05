<?php

require '../vendor/autoload.php';

$configuration = new \TrueNorth\opencfp\Configuration();

// Create our two Sentry groups
class_alias('Cartalyst\Sentry\Facades\Native\Sentry', 'Sentry');
$dsn = $configuration->getPDODSN();
$user = $configuration->getMySQLUser();
$password = $configuration->getMySQLPassword();
Sentry::setupDatabaseResolver(new PDO($dsn, $user, $password));
$group = Sentry::getGroupProvider()->create(
    array(
        'name' => 'Speakers',
        'permissions' => array(
            'admin' => 0,
            'users' => 1
        )
    )
);
Sentry::getGroupProvider()->create(
    array(
        'name' => 'Admin',
        'permissions' => array(
            'admin' => 1,
            'users' => 0,
        )
    )
);
