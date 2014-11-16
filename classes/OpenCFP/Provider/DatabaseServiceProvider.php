<?php namespace OpenCFP\Provider; 

use Silex\Application;
use Silex\ServiceProviderInterface;

class DatabaseServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        $app['db'] = new \PDO(
            $app->config('database.dsn'),
            $app->config('database.user'),
            $app->config('database.password')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
    }
}