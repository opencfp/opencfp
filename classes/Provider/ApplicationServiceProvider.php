<?php

namespace OpenCFP\Provider;

use Cartalyst\Sentinel\Sentinel;
use Illuminate\Database\Capsule\Manager as Capsule;
use OpenCFP\Application\Speakers;
use OpenCFP\Domain\CallForProposal;
use OpenCFP\Domain\Model\Airport;
use OpenCFP\Domain\Model\User;
use OpenCFP\Domain\Services\AirportInformationDatabase;
use OpenCFP\Domain\Services\EventDispatcher;
use OpenCFP\Domain\Speaker\SpeakerRepository;
use OpenCFP\Infrastructure\Auth\Contracts\AccountManagement;
use OpenCFP\Infrastructure\Auth\Contracts\Authentication;
use OpenCFP\Infrastructure\Auth\Contracts\IdentityProvider;
use OpenCFP\Infrastructure\Auth\SentinelAccountManagement;
use OpenCFP\Infrastructure\Auth\SentinelAuthentication;
use OpenCFP\Infrastructure\Auth\SentinelIdentityProvider;
use OpenCFP\Infrastructure\Crypto\PseudoRandomStringGenerator;
use OpenCFP\Infrastructure\Persistence\IlluminateSpeakerRepository;
use OpenCFP\Infrastructure\Persistence\IlluminateTalkRepository;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ApplicationServiceProvider implements ServiceProviderInterface
{
    /**s
     * {@inheritdoc}
     */
    public function register(Container $app)
    {
        $app[AccountManagement::class] = function ($app) {
            return new SentinelAccountManagement($app[Sentinel::class]);
        };

        $app[IdentityProvider::class] = function ($app) {
            return new SentinelIdentityProvider($app[Sentinel::class], $app[SpeakerRepository::class]);
        };

        $app[Authentication::class] = function ($app) {
            return new SentinelAuthentication($app[Sentinel::class], $app[AccountManagement::class]);
        };

        $app[SpeakerRepository::class] = function () {
            return new IlluminateSpeakerRepository(new User());
        };

        $app[Capsule::class] = function ($app) {
            $capsule = new Capsule();

            $capsule->addConnection([
                'driver'    => 'mysql',
                'host'      => $app->config('database.host'),
                'database'  => $app->config('database.database'),
                'username'  => $app->config('database.user'),
                'password'  => $app->config('database.password'),
                'charset'   => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix'    => '',
            ]);

            return $capsule;
        };

        $app['application.speakers'] = function ($app) {
            return new Speakers(
                new CallForProposal(new \DateTimeImmutable($app->config('application.enddate'))),
                $app[IdentityProvider::class],
                $app[SpeakerRepository::class],
                new IlluminateTalkRepository(),
                new EventDispatcher()
            );
        };

        $app[AirportInformationDatabase::class] = function () {
            return new Airport();
        };

        $app['security.random'] = function () {
            return new PseudoRandomStringGenerator();
        };
    }
}
