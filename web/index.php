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

require_once __DIR__ . '/../vendor/autoload.php';

use OpenCFP\Application;
use OpenCFP\Environment;
use Symfony\Component\Debug\Debug;

$basePath    = \realpath(\dirname(__DIR__));
$environment = Environment::fromServer($_SERVER);

if (!$environment->isProduction()) {
    Debug::enable();
}

$app = new Application($basePath, $environment);

$app->run();
