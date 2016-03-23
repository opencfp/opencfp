<?php

namespace OpenCFP\Provider;

use Illuminate\Database\Capsule\Manager as Capsule;
use League\OAuth2\Server\ResourceServer;
use OpenCFP\Application\Speakers;
use OpenCFP\Domain\CallForProposal;
use OpenCFP\Domain\Services\AirportInformationDatabase;
use OpenCFP\Domain\Services\EventDispatcher;
use OpenCFP\Infrastructure\Auth\OAuthIdentityProvider;
use OpenCFP\Infrastructure\Auth\SentryIdentityProvider;
use OpenCFP\Infrastructure\Crypto\PseudoRandomStringGenerator;
use OpenCFP\Infrastructure\OAuth\AccessTokenStorage;
use OpenCFP\Infrastructure\OAuth\ClientStorage;
use OpenCFP\Infrastructure\OAuth\ScopeStorage;
use OpenCFP\Infrastructure\OAuth\SessionStorage;
use OpenCFP\Infrastructure\Persistence\IlluminateAirportInformationDatabase;
use OpenCFP\Infrastructure\Persistence\SpotSpeakerRepository;
use OpenCFP\Infrastructure\Persistence\SpotTalkRepository;
use RandomLib\Factory;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Spot\Locator;

class ApplicationServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        $app['application.speakers'] = $app->share(function ($app) {
            /* @var Locator $spot */
            $spot = $app['spot'];
            
            $userMapper = $spot->mapper(\OpenCFP\Domain\Entity\User::class);
            $talkMapper = $spot->mapper(\OpenCFP\Domain\Entity\Talk::class);
            $speakerRepository = new SpotSpeakerRepository($userMapper);

            return new Speakers(
                new CallForProposal(new \DateTime($app->config('application.enddate'))),
                new SentryIdentityProvider($app['sentry'], $speakerRepository),
                $speakerRepository,
                new SpotTalkRepository($talkMapper),
                new EventDispatcher()
            );
        });

        $app[AirportInformationDatabase::class] = $app->share(function ($app) {
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

            return new IlluminateAirportInformationDatabase($capsule);
        });

        $app['security.random'] = $app->share(function ($app) {
            return new PseudoRandomStringGenerator(new Factory());
        });

        $app['oauth.resource'] = $app->share(function ($app) {
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
        });

        $app['application.speakers.api'] = $app->share(function ($app) {
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
        });
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
    }
}
