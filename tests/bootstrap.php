<?php

// Boostrap setting a few things for our tests
if (!defined('APP_DIR')) {
	define('APP_DIR', __DIR__ . '/../web'); 
}

// Grab our autoloader
require APP_DIR . '/vendor/autoload.php';

// Load out mock for tests that use PDO
require './PDOMock.php';
