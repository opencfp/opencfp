<?php namespace OpenCFP\Provider;

use Cartalyst\Sentry\Facades\Native\Sentry;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Illuminate\Database\Capsule\Manager as Capsule;

class SentryServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        // Create a new Database connection
        $database = new Capsule;

        $database->addConnection(array(
            'driver'    => 'mysql',
            'host'      => $app->config('database.host'),
            'database'  => $app->config('database.database'),
            'username'  => $app->config('database.user'),
            'password'  => $app->config('database.password'),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci'
        ));

        // Makes the new "capsule" the global static instance.
        $database->setAsGlobal();

        // Boots Eloquent to be used by Sentry.
        $database->bootEloquent();

        $app['sentry'] = $app->share(function () {
            $sentry = Sentry::instance();
            $sentry->getThrottleProvider()->disable();

            return $sentry;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
    }
}
