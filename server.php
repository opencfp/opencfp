<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

\define('WEB_PATH', __DIR__ . '/web/');

$path = WEB_PATH . $_SERVER['REQUEST_URI'];

if (\is_file($path)) {
    return false;
}

require_once WEB_PATH . 'index_dev.php';
