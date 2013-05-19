<?php

// Bootstrap for OpenCFP
require 'vendor/autoload.php';
define('APP_DIR', __DIR__);

// start our session
session_start();

/**
 * Database and Sentry configuration done here
 * CHANGE THIS TO MATCH YOUR PREFERRED USERNAME AND PASSWORD 
 */
class_alias('Cartalyst\Sentry\Facades\Native\Sentry', 'Sentry');
$dsn = "mysql:dbname=cfp;host=localhost";
$user = "root";
$password = "";
$db = new \PDO($dsn, $user, $password);

Sentry::setupDatabaseResolver($db);

// Initialize Twig
$loader = new Twig_Loader_Filesystem('../templates');
$twig = new Twig_Environment($loader);

