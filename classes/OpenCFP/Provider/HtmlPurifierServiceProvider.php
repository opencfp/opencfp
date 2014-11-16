<?php namespace OpenCFP\Provider; 

use HTMLPurifier;
use HTMLPurifier_Config;
use Silex\Application;
use Silex\ServiceProviderInterface;

class HtmlPurifierServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        $app['purifier'] = $app->share(function($app){
            $config = HTMLPurifier_Config::createDefault();

            if ($app->config('cache.enabled')) {
                $cacheDirectory = $app->config('paths.cache.purifier');

                if (!is_dir($cacheDirectory)) {
                    mkdir($cacheDirectory, 0755, true);
                }

                $config->set('Cache.SerializerPath', $cacheDirectory);
            }

            return new HTMLPurifier($config);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
    }
}