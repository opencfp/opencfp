<?php namespace OpenCFP\Provider;

use Illuminate\Database\Capsule\Manager as Capsule;
use OpenCFP\Util\Wrapper\SentinelWrapper;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class SentinelServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $app
     */
    public function register(Container $app)
    {
        // Create a new Database connection
        $database = new Capsule;
        $database->addConnection([
            'driver'    => 'mysql',
            'host'      => $app->config('database.host'),
            'database'  => $app->config('database.database'),
            'username'  => $app->config('database.user'),
            'password'  => $app->config('database.password'),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
        ]);

        // Makes the new "capsule" the global static instance.
        $database->setAsGlobal();

        // Boots Eloquent to be used by Sentry.
        $database->bootEloquent();

        $app['sentinel'] = function ($app) {
            return new SentinelWrapper($app['env']);
        };
    }
}
