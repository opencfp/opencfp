<?php

require '../vendor/autoload.php';

$configuration = new \TrueNorth\OpenCFP\Configuration();
$database = new \TrueNorth\OpenCFP\Database();

// Create our two Sentry groups
class_alias('Cartalyst\Sentry\Facades\Native\Sentry', 'Sentry');
Sentry::setupDatabaseResolver($database->getPDO());
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
