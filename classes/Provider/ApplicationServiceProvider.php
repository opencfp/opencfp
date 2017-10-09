<?php

namespace OpenCFP\Provider;

use Cartalyst\Sentry\Sentry;
use Illuminate\Database\Capsule\Manager as Capsule;
use League\OAuth2\Server\ResourceServer;
use OpenCFP\Application\Speakers;
use OpenCFP\Domain\CallForProposal;
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
use OpenCFP\Infrastructure\Persistence\IlluminateAirportInformationDatabase;
use OpenCFP\Infrastructure\Persistence\SpotSpeakerRepository;
use OpenCFP\Infrastructure\Persistence\SpotTalkRepository;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use RandomLib\Factory;
use Spot\Locator;

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
            $userMapper = $app['spot']->mapper(\OpenCFP\Domain\Entity\User::class);
            return new SentryIdentityProvider($app['sentry'], new SpotSpeakerRepository($userMapper));
        };

        $app[Authentication::class] = function ($app) {
            return new SentryAuthentication($app['sentry']);
        };

        $app[Capsule::class] = function ($app) {
            $capsule = new Capsule;

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

            $capsule->setAsGlobal();
            $capsule->bootEloquent();
            return $capsule;
        };

        $app['application.speakers'] = function ($app) {
            /* @var Locator $spot */
            $spot = $app['spot'];
            
            $userMapper = $spot->mapper(\OpenCFP\Domain\Entity\User::class);
            $talkMapper = $spot->mapper(\OpenCFP\Domain\Entity\Talk::class);
            $speakerRepository = new SpotSpeakerRepository($userMapper);

            /* @var Sentry $sentry */
            $sentry = $app['sentry'];
            
            return new Speakers(
                new CallForProposal(new \DateTime($app->config('application.enddate'))),
                new SentryIdentityProvider($sentry, $speakerRepository),
                $speakerRepository,
                new SpotTalkRepository($talkMapper),
                new EventDispatcher()
            );
        };

        $app[AirportInformationDatabase::class] = function ($app) {
            return new IlluminateAirportInformationDatabase($app[Capsule::class]);
        };

        $app['security.random'] = function ($app) {
            return new PseudoRandomStringGenerator(new Factory());
        };

        $app['oauth.resource'] = function ($app) {
            $sessionStorage = new SessionStorage();
            $accessTokenStorage = new AccessTokenStorage();
            $clientStorage = new ClientStorage();
            $scopeStorage = new ScopeStorage();

            $server = new ResourceServer(
                $sessionStorage,
                $accessTokenStorage,
                $clientStorage,
                $scopeStorage
            );

            return $server;
        };

        $app['application.speakers.api'] = function ($app) {
            /* @var Locator $spot */
            $spot = $app['spot'];
            
            $userMapper = $spot->mapper(\OpenCFP\Domain\Entity\User::class);
            $talkMapper = $spot->mapper(\OpenCFP\Domain\Entity\Talk::class);
            $speakerRepository = new SpotSpeakerRepository($userMapper);

            return new Speakers(
                new CallForProposal(new \DateTime($app->config('application.enddate'))),
                new OAuthIdentityProvider($app['oauth.resource'], $speakerRepository),
                $speakerRepository,
                new SpotTalkRepository($talkMapper),
                new EventDispatcher()
            );
        };
    }
}
