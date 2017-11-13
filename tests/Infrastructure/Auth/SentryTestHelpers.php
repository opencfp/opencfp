<?php

namespace OpenCFP\Test\Infrastructure\Auth;

use OpenCFP\Provider\SymfonySentrySession;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;

trait SentryTestHelpers
{
    public function getSentry()
    {
        $hasher           = new \Cartalyst\Sentry\Hashing\NativeHasher;
        $userProvider     = new \Cartalyst\Sentry\Users\Eloquent\Provider($hasher);
        $groupProvider    = new \Cartalyst\Sentry\Groups\Eloquent\Provider;
        $throttleProvider = new \Cartalyst\Sentry\Throttling\Eloquent\Provider($userProvider);
        $session          = new SymfonySentrySession(new Session(new MockFileSessionStorage()));
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
    }
}
