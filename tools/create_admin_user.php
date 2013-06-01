<?php

require __DIR__ . '/../bootstrap.php';

use CHH\Optparse;

$parser = new Optparse\Parser();
$parser->addFlag("help");
$parser->addArgument("email", array('required' => true));
$parser->addArgument("password", array('required' => true));

try {
    $parser->parse();
} catch (Optparse\Exception $e) {
    echo $parser->usage() . "\n";
    exit(1);
}

$user = Sentry::getUserProvider()->create(array(
    'email' => $parser['email'],
		'password' => $parser['password'],
		'activated' => 1
));

$adminGroup = Sentry::getGroupProvider()->findByName('Admin');
$user->addGroup($adminGroup);

echo "Done\n";
exit();

