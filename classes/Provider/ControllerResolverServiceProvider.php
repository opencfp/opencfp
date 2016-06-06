<?php namespace OpenCFP\Provider;

use Pimple\Container;
use Silex\Application;
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
