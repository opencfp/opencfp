<?php

// Boostrap setting a few things for our tests
define('APP_DIR', __DIR__ . '/../web'); 

require APP_DIR . '/vendor/autoload.php';

// start our session
session_start();

// Database config stuff loaded here
class_alias('Cartalyst\Sentry\Facades\Native\Sentry', 'Sentry');
$dsn = "mysql:dbname=cfp;host=localhost";
$user = "root";
Sentry::setupDatabaseResolver(new PDO($dsn, $user));


// Initialize Twig
$loader = new Twig_Loader_Filesystem(APP_DIR . '/templates');
$twig = new Twig_Environment($loader);