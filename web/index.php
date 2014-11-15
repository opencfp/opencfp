<?php

require_once '../vendor/autoload.php';

$app = new \OpenCFP\Application(realpath(dirname(__DIR__)));
$app->boot();

$app->run();
