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

use OpenCFP\Domain\Services\ResetEmailer;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

final class ResetEmailerServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $app)
    {
        $app['reset_emailer'] = function ($app) {
            return new ResetEmailer(
                $app['mailer'],
                $app['twig'],
                $app->config('application.email'),
                $app->config('application.title')
            );
        };
    }
}
