<?php

define('APP_DIR', __DIR__);
if (!file_exists(APP_DIR . '/vendor/autoload.php')) {
    throw new Exception('Autoload file does not exist.  Did you run composer install?');
}


// Bootstrap for OpenCFP
require APP_DIR . '/vendor/autoload.php';

$config = new OpenCFP\Configuration(new OpenCFP\ConfigINIFileLoader(APP_DIR . '/config/config.ini'));

// start our session
session_start();

/**
 * Sentry configuration done here
 */
class_alias('Cartalyst\Sentry\Facades\Native\Sentry', 'Sentry');
$db = new \PDO($config->getPDODSN(), $config->getPDOUser(), $config->getPDOPassword());
Sentry::setupDatabaseResolver($db);

// Initialize Twig
$loader = new Twig_Loader_Filesystem(APP_DIR . $config->getTwigTemplateDir());
$twig = new Twig_Environment($loader);

