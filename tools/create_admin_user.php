<?php

require '../classes/OpenCFP/Bootstrap.php';

$bootstrap = new \OpenCFP\Bootstrap();
$app = $bootstrap->getApp();

$parser = new \CHH\Optparse\Parser();
$parser->addFlag("help");
$parser->addFlag("update");

$parser->addArgument("email", array('required' => true));
$parser->addArgument("password", array('required' => false));

try {
    $parser->parse();
} catch (\CHH\Optparse\Exception $e) {
    echo $parser->usage() . "\n";
    exit(1);
}

if ($parser['update']) {
    $user = $app['sentry']->getUserProvider()->findByLogin($parser['email']);

    if ($user->hasAccess('Admin')) {
        echo "The user {$parser['email']} already has Admin access\n\n";
        exit(1);
    }

    $adminGroup = $app['sentry']->getGroupProvider()->findByName('Admin');
    $user->addGroup($adminGroup);
    echo "Added {$parser['email']} to the Admin group\n\n";
    exit(0);
}

/**
 * If we are creating a new account we have to make sure that we have a 
 * password
 */
if ($parser['password'] == '') {
    echo "When creating new accounts, a password is required";
    exit(1);
}

$user = $app['sentry']->getUserProvider()->create(array(
    'email' => $parser['email'],
		'password' => $parser['password'],
		'activated' => 1
));

$adminGroup = $app['sentry']->getGroupProvider()->findByName('Admin');
$user->addGroup($adminGroup);

echo "Done\n";
exit();

