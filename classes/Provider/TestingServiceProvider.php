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

namespace OpenCFP\Provider;

use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Test\Helper\MockableAuthenticator;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

final class TestingServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app->extend(Authentication::class, function (Authentication $authentication) {
            return new MockableAuthenticator($authentication);
        });
    }
}
