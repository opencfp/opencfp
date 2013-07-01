<?php

// Grab our autoloader
require __DIR__.'/../vendor/autoload.php';

// Load out mock for tests that use PDO
require __DIR__.'/PDOMock.php';

// Initialize Twig
$loader = new Twig_Loader_Filesystem(__DIR__.'/../templates');
$twig = new Twig_Environment($loader);
