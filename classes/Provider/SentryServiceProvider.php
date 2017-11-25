<?php

namespace OpenCFP\Provider;

use Cartalyst\Sentry\Facades\Native\Sentry;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class SentryServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $app)
    {
        $app['sentry'] = function ($app) {
            $hasher           = new \Cartalyst\Sentry\Hashing\NativeHasher();
            $userProvider     = new \Cartalyst\Sentry\Users\Eloquent\Provider($hasher);
            $groupProvider    = new \Cartalyst\Sentry\Groups\Eloquent\Provider();
            $throttleProvider = new \Cartalyst\Sentry\Throttling\Eloquent\Provider($userProvider);
            $session          = new SymfonySentrySession($app['session']);
            $cookie           = new \Cartalyst\Sentry\Cookies\NativeCookie([]);

            $sentry = new \Cartalyst\Sentry\Sentry(
                $userProvider,
                $groupProvider,
                $throttleProvider,
                $session,
                $cookie
            );

            $throttleProvider->disable();

            return $sentry;
        };
    }
}
