<?php

namespace OpenCFP\Provider;

use Cartalyst\Sentinel\Activations\IlluminateActivationRepository;
use Cartalyst\Sentinel\Hashing\NativeHasher;
use Cartalyst\Sentinel\Persistences\IlluminatePersistenceRepository;
use Cartalyst\Sentinel\Roles\IlluminateRoleRepository;
use Cartalyst\Sentinel\Sentinel;
use Cartalyst\Sentinel\Users\IlluminateUserRepository;
use Illuminate\Contracts\Events\Dispatcher;
use OpenCFP\Infrastructure\Persistence\NullCookie;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

final class SentinelServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app[Sentinel::class] = function ($app) {
            return new Sentinel(
                new IlluminatePersistenceRepository(
                    new SymfonySentinelSession($app['session']),
                    new NullCookie()
                ),
                new IlluminateUserRepository(
                    new NativeHasher(),
                    $app[Dispatcher::class]
                ),
                new IlluminateRoleRepository(),
                new IlluminateActivationRepository(),
                $app[Dispatcher::class]
            );
        };

        $app[Dispatcher::class] = function () {
            return new \Illuminate\Events\Dispatcher();
        };
    }
}
