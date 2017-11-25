<?php

namespace OpenCFP\Provider;

use Cartalyst\Sentry\Sentry;
use Illuminate\Database\Capsule\Manager as Capsule;
use League\OAuth2\Server\ResourceServer;
use OpenCFP\Application\Speakers;
use OpenCFP\Domain\CallForProposal;
use OpenCFP\Domain\Model\Airport;
use OpenCFP\Domain\Model\User;
use OpenCFP\Domain\Services\AccountManagement;
use OpenCFP\Domain\Services\AirportInformationDatabase;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Domain\Services\EventDispatcher;
use OpenCFP\Domain\Services\IdentityProvider;
use OpenCFP\Infrastructure\Auth\OAuthIdentityProvider;
use OpenCFP\Infrastructure\Auth\SentryAccountManagement;
use OpenCFP\Infrastructure\Auth\SentryAuthentication;
use OpenCFP\Infrastructure\Auth\SentryIdentityProvider;
use OpenCFP\Infrastructure\Crypto\PseudoRandomStringGenerator;
use OpenCFP\Infrastructure\OAuth\AccessTokenStorage;
use OpenCFP\Infrastructure\OAuth\ClientStorage;
use OpenCFP\Infrastructure\OAuth\ScopeStorage;
use OpenCFP\Infrastructure\OAuth\SessionStorage;
use OpenCFP\Infrastructure\Persistence\IlluminateSpeakerRepository;
use OpenCFP\Infrastructure\Persistence\IlluminateTalkRepository;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ApplicationServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $app)
    {
        $app[AccountManagement::class] = function ($app) {
            return new SentryAccountManagement($app['sentry']);
        };

        $app[IdentityProvider::class] = function ($app) {
            return new SentryIdentityProvider($app['sentry'], new IlluminateSpeakerRepository(new User()));
        };

        $app[Authentication::class] = function ($app) {
            return new SentryAuthentication($app['sentry']);
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
            $speakerRepository = new IlluminateSpeakerRepository(new User());

            /* @var Sentry $sentry */
            $sentry = $app['sentry'];
            
            return new Speakers(
                new CallForProposal(new \DateTimeImmutable($app->config('application.enddate'))),
                new SentryIdentityProvider($sentry, $speakerRepository),
                $speakerRepository,
                new IlluminateTalkRepository(),
                new EventDispatcher()
            );
        };

        $app[AirportInformationDatabase::class] = function ($app) {
            return new Airport();
        };

        $app['security.random'] = function () {
            return new PseudoRandomStringGenerator();
        };

        $app['oauth.resource'] = function ($app) {
            $sessionStorage     = new SessionStorage();
            $accessTokenStorage = new AccessTokenStorage();
            $clientStorage      = new ClientStorage();
            $scopeStorage       = new ScopeStorage();

            $server = new ResourceServer(
                $sessionStorage,
                $accessTokenStorage,
                $clientStorage,
                $scopeStorage
            );

            return $server;
        };

        $app['application.speakers.api'] = function ($app) {
            $speakerRepository = new IlluminateSpeakerRepository(new User());

            return new Speakers(
                new CallForProposal(new \DateTimeImmutable($app->config('application.enddate'))),
                new OAuthIdentityProvider($app['oauth.resource'], $speakerRepository),
                $speakerRepository,
                new IlluminateTalkRepository(),
                new EventDispatcher()
            );
        };
    }
}
