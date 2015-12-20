<?php namespace OpenCFP\Provider;

use Cartalyst\Sentry\Facades\Native\Sentry;
use Illuminate\Database\Capsule\Manager as Capsule;
use Silex\Application;
use Silex\ServiceProviderInterface;

class SentryServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
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

        $app['sentry'] = $app->share(function ($app) {
            $hasher = new \Cartalyst\Sentry\Hashing\NativeHasher;
            $userProvider = new \Cartalyst\Sentry\Users\Eloquent\Provider($hasher);
            $groupProvider = new \Cartalyst\Sentry\Groups\Eloquent\Provider;
            $throttleProvider = new \Cartalyst\Sentry\Throttling\Eloquent\Provider($userProvider);
            $session = new SymfonySentrySession($app['session']);
            $cookie = new \Cartalyst\Sentry\Cookies\NativeCookie([]);

            $sentry = new \Cartalyst\Sentry\Sentry(
                $userProvider,
                $groupProvider,
                $throttleProvider,
                $session,
                $cookie
            );

            Sentry::setupDatabaseResolver($app['db']);
            $throttleProvider->disable();

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
