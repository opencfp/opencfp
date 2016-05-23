<?php

define('WEB_PATH', __DIR__ . '/web/');

if (preg_match('/\.css|\.js|\.jpg|\.png|\.map|\.svg|\.ico$/', $_SERVER['REQUEST_URI'], $match)) {
    $mimeTypes = [
        '.css' => 'text/css',
        '.js'  => 'application/javascript',
        '.ico' => 'image/ico',
        '.jpg' => 'image/jpg',
        '.png' => 'image/png',
        '.svg' => 'image/svg',
        '.map' => 'application/json',
    ];

    $path = WEB_PATH . $_SERVER['REQUEST_URI'];
    if (is_file($path)) {
        header("Content-Type: {$mimeTypes[$match[0]]}");
        require $path;
        exit;
    }
}

require_once WEB_PATH . 'index_dev.php';
