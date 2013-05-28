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

$config = parse_ini_file(APP_DIR . '/config/config.ini', true);

// start our session
session_start();

/**
 * Database and Sentry configuration done here
 * CHANGE THIS TO MATCH YOUR PREFERRED USERNAME AND PASSWORD
 */
class_alias('Cartalyst\Sentry\Facades\Native\Sentry', 'Sentry');
$db = new \PDO($config['database']['dsn'], $config['database']['user'], $config['database']['password']);
Sentry::setupDatabaseResolver($db);

// Initialize Twig
$loader = new Twig_Loader_Filesystem(APP_DIR . $config['twig']['template_dir']);
$twig = new Twig_Environment($loader);

