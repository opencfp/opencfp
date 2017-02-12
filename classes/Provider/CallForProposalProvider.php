<?php namespace OpenCFP\Provider;

use OpenCFP\Domain\CallForProposal;
use Silex\Application;
use Silex\ServiceProviderInterface;

class CallForProposalProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
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
