<?php
require_once '../classes/OpenCFP/Bootstrap.php';

$bootstrap = new \OpenCFP\Bootstrap();
$app = $bootstrap->getApp();

define('APP_DIR', dirname(dirname(__DIR__)));
define('UPLOAD_PATH', $app['uploadPath']);

$app->run();