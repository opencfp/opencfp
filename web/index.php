<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2018 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */
require_once __DIR__ . '/../vendor/autoload.php';

use OpenCFP\Environment;
use OpenCFP\Kernel;
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

$basePath    = \realpath(\dirname(__DIR__));
$environment = Environment::fromServer($_SERVER);

if (!$environment->isProduction()) {
    Debug::enable();
}

$kernel   = new Kernel((string) $environment, !$environment->isProduction());
$request  = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
