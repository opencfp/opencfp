<?php

require __DIR__ . '/../bootstrap.php';

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
