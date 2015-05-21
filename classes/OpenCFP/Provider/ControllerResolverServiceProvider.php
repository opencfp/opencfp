<?php namespace OpenCFP\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

class ControllerResolverServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        $app['resolver'] = $app->share(function() use ($app) {
            return new ControllerResolver($app);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
    }
}
