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

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ControllerResolverServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $app)
    {
        $app['resolver'] = function () use ($app) {
            return new ControllerResolver($app);
        };
    }
}
