<?php

namespace OpenCFP\Provider;

use Pimple\Container;
use Silex\Application;
use Pimple\ServiceProviderInterface;
use Spot\Config as SpotConfig;
use Spot\Locator as SpotLocator;

class SpotServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $app)
    {
        $app['spot'] = function ($app) {
            $config = new SpotConfig();
            $dbConfig = [
                'dbname' => $app->config('database.database'),
                'user' => $app->config('database.user'),
                'password' => $app->config('database.password'),
                'host' => $app->config('database.host'),
                'driver' => 'pdo_mysql',
            ];

            if ($app->config('database.port') !== null) {
                $dbConfig['port'] = $app->config('database.port');
            }

            $config->addConnection('mysql', $dbConfig);

            return new SpotLocator($config);
        };
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
    }
}
