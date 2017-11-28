<?php

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Helper;

use OpenCFP\Provider\SymfonySentrySession;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;

trait SentryTestHelpers
{
    public function getSentry(): \Cartalyst\Sentry\Sentry
    {
        $hasher           = new \Cartalyst\Sentry\Hashing\NativeHasher();
        $userProvider     = new \Cartalyst\Sentry\Users\Eloquent\Provider($hasher);
        $groupProvider    = new \Cartalyst\Sentry\Groups\Eloquent\Provider();
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
