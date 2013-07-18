<?php

namespace OpenCFP\ServiceProvider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Cartalyst\Sentry\Sentry;
use Cartalyst\Sentry\Cookies\NativeCookie;
use Cartalyst\Sentry\Hashing\NativeHasher;
use Cartalyst\Sentry\Groups\Eloquent\Provider as GroupProvider;
use Cartalyst\Sentry\Sessions\NativeSession;
use Cartalyst\Sentry\Throttling\Eloquent\Provider as ThrottleProvider;
use Cartalyst\Sentry\Users\Eloquent\Provider as UserProvider;

/**
 * Sentry integration into Silex.
 *
 * @author Hugo Hamon <hugo.hamon@sensiolabs.com>
 */
class SentryServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        // Define Sentry global configuration parameters
        // These parameters configure the services
        $app['sentry.session.key']              = null;
        $app['sentry.group_provider.model']     = null;
        $app['sentry.throttle_provider.model']  = null;
        $app['sentry.user_provider.model']      = null;
        $app['sentry.cookie.key']               = null;
        $app['sentry.cookie.options']           = array();

        // Define the Sentry services
        $app['sentry'] = $app->share(function() use ($app) {
            return new Sentry(
                $app['sentry.user_provider'],
                $app['sentry.group_provider'],
                $app['sentry.throttle_provider'],
                $app['sentry.session'],
                $app['sentry.cookie'],
                $app['request']->getClientIp()
            );
        });

        $app['sentry.hasher'] = $app->share(function() {
            return new NativeHasher();
        });

        $app['sentry.user_provider'] = $app->share(function() use ($app) {
            return new UserProvider($app['sentry.hasher'], $app['sentry.user_provider.model']);
        });

        $app['sentry.throttle_provider'] = $app->share(function() use ($app) {
            return new ThrottleProvider(
                $app['sentry.user_provider'],
                $app['sentry.throttle_provider.model']
            );
        });

        $app['sentry.group_provider'] = $app->share(function() use ($app) {
            return new GroupProvider($app['sentry.group_provider.model']);
        });

        $app['sentry.session'] = $app->share(function() use ($app) {
            return new NativeSession($app['sentry.session.key']);
        });

        $app['sentry.cookie'] = $app->share(function() use ($app) {
            return new NativeCookie($app['sentry.cookie.options'], $app['sentry.cookie.key']);
        });
    }

    public function boot(Application $app)
    {
    }
}
