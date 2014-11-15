<?php

use OpenCFP\Application;
use OpenCFP\Environment;

require_once '../vendor/autoload.php';

$basePath = realpath(dirname(__DIR__));
$environment = Environment::fromEnvironmentVariable();

$app = new Application($basePath, $environment);

$app->run();
