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

$kernel  = new Kernel((string) $environment, !$environment->isProduction());
$request = Request::createFromGlobals();

if (\getenv('TRUST_PROXIES') ? \filter_var(\getenv('TRUST_PROXIES'), FILTER_VALIDATE_BOOLEAN) : false) {
    Request::setTrustedProxies(
        // trust *all* requests
        ['127.0.0.1', $request->server->get('REMOTE_ADDR')],
        Request::HEADER_X_FORWARDED_ALL
    );
}

$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
