<?php

namespace OpenCFP\Provider;

use Cartalyst\Sentinel\Activations\IlluminateActivationRepository;
use Cartalyst\Sentinel\Hashing\NativeHasher;
use Cartalyst\Sentinel\Persistences\IlluminatePersistenceRepository;
use Cartalyst\Sentinel\Reminders\IlluminateReminderRepository;
use Cartalyst\Sentinel\Roles\IlluminateRoleRepository;
use Cartalyst\Sentinel\Sentinel;
use Cartalyst\Sentinel\Throttling\IlluminateThrottleRepository;
use Cartalyst\Sentinel\Users\IlluminateUserRepository;
use Cartalyst\Sentinel\Users\UserRepositoryInterface;
use Illuminate\Contracts\Events\Dispatcher;
use OpenCFP\Infrastructure\Persistence\NullCookie;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

final class SentinelServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app[Sentinel::class] = function ($app) {
            $sentinel = new Sentinel(
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
            $sentinel->setReminderRepository(new IlluminateReminderRepository($app[UserRepositoryInterface::class]));
            $sentinel->setThrottleRepository(new IlluminateThrottleRepository());

            return $sentinel;
        };

        $app[Dispatcher::class] = function () {
            return new \Illuminate\Events\Dispatcher();
        };

        $app[UserRepositoryInterface::class] = function ($app) {
            return new IlluminateUserRepository(
                new NativeHasher(),
                $app[Dispatcher::class]
            );
        };
    }
}
