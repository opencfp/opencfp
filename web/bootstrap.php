<?php

// Bootstrap for OpenCFP
require 'vendor/autoload.php';

// start our session
session_start();

// Database config stuff loaded here
class_alias('Cartalyst\Sentry\Facades\Native\Sentry', 'Sentry');
$dsn = "mysql:dbname=cfp;host=localhost";
$user = "root";
Sentry::setupDatabaseResolver(new PDO($dsn, $user));


// Initialize Twig
$loader = new Twig_Loader_Filesystem('./templates');
$twig = new Twig_Environment($loader);

