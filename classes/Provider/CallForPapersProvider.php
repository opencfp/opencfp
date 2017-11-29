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

use OpenCFP\Domain\CallForPapers;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Application;

class CallForPapersProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $app)
    {
        $cfp = new CallForPapers(new \DateTimeImmutable($app->config('application.enddate')));

        $app['callforpapers'] = $cfp;
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
    }
}
