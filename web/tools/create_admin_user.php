<?php
require '../vendor/autoload.php';

use CHH\Optparse;

// Create our two Sentry groups
class_alias('Cartalyst\Sentry\Facades\Native\Sentry', 'Sentry');
$dsn = "mysql:dbname=cfp;host=localhost";
$user = "root";
Sentry::setupDatabaseResolver(new PDO($dsn, $user));

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
    'password' => $parser['password']
));

$adminGroup = Sentry::getGroupProvider()->findByName('Admin');
$user->addGroup($adminGroup);

echo "Done\n";
exit();



