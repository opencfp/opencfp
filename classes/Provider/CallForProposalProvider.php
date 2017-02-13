<?php namespace OpenCFP\Provider;

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
