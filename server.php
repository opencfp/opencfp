<?php

define('WEB_PATH', __DIR__ . '/web/');

$path = WEB_PATH . $_SERVER['REQUEST_URI'];
if (is_file($path)) {
    return false;
}

require_once WEB_PATH . 'index_dev.php';
