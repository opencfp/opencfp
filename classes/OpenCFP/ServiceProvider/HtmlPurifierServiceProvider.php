<?php

namespace OpenCFP\ServiceProvider;

use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * HTMLPurifier integration into Silex.
 *
 * @author Hugo Hamon <hugo.hamon@sensiolabs.com>
 */
class HtmlPurifierServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        // Define HTMLPurifier global configuration parameters
        // These parameters configure the services
        $app['htmlpurifier.cache'] = null;

        // Define the HTMLPurifier services
        $app['purifier'] = $app->share(function() use ($app) {
            return new \HTMLPurifier($app['purifier.config']);
        });

        $app['purifier.config'] = $app->share(function() use ($app) {
            $config = \HTMLPurifier_Config::createDefault();
            if (null !== $app['htmlpurifier.cache']) {
                $config->set('Cache.SerializerPath', $app['htmlpurifier.cache']);
            }

            return $config;
        });
    }

    public function boot(Application $app)
    {
    }
}
