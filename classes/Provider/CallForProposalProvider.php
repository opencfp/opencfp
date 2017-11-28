<?php

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Provider;

use OpenCFP\Domain\CallForProposal;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Application;

class CallForProposalProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $app)
    {
        $cfp = new CallForProposal(new \DateTimeImmutable($app->config('application.enddate')));

        $app['callforproposal'] = $cfp;
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
    }
}
