<?php
require_once '../classes/OpenCFP/Bootstrap.php';

// define('APP_DIR', dirname(dirname(__DIR__)));
//define('APP_DIR', dirname(__DIR__));

$bootstrap = new \OpenCFP\Bootstrap();
$app = $bootstrap->getApp();

define('UPLOAD_PATH', $app['uploadPath']);

$app->run();