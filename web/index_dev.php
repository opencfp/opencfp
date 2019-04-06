<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2019 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

$filename = __DIR__ . \preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);

if (PHP_SAPI === 'cli-server' && \is_file($filename)) {
    return false;
}

require_once __DIR__ . '/index.php';
