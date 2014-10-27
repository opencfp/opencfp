<?php

// Grab our autoloader
require './vendor/autoload.php';

// Load out mock for tests that use PDO
require 'PDOMock.php';

// Initialize Twig
$loader = new Twig_Loader_Filesystem('./templates');
$twig = new Twig_Environment($loader);
