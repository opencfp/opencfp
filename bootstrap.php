<?php

define('APP_DIR', __DIR__);
if (!file_exists(APP_DIR . '/vendor/autoload.php')) {
    throw new Exception('Autoload file does not exist.  Did you run composer install?');
}

if (!file_exists(APP_DIR . '/config/config.ini')) {
    throw new Exception('Config file does not exist.  Did you copy the config.ini.dist to config.ini?');
}
// Bootstrap for OpenCFP
require APP_DIR . '/vendor/autoload.php';

$config = new OpenCFP\ConfigINIFileLoader(APP_DIR . '/config/config.ini');
$configData = $config->load();

// Place our info into Pimple
$container = new Pimple();

foreach ($configData as $category => $info) {
    foreach ($info as $key => $value) {
        $container["{$category}.{$key}"] = $value;
    }
}

// start our session
session_start();

/**
 * Sentry configuration done here
 */
class_alias('Cartalyst\Sentry\Facades\Native\Sentry', 'Sentry');
$db = new \PDO(
    $container['database.dsn'], 
    $container['database.user'],
    $container['database.password']
);
Sentry::setupDatabaseResolver($db);

// Initialize Twig
$loader = new Twig_Loader_Filesystem(APP_DIR . $container['twig.template_dir']);
$twig = new Twig_Environment($loader);

