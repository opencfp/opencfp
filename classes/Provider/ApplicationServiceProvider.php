<?php

namespace OpenCFP\Provider;

use League\OAuth2\Server\ResourceServer;
use OpenCFP\Application\Speakers;
use OpenCFP\Domain\CallForProposal;
use OpenCFP\Domain\Services\EventDispatcher;
use OpenCFP\Infrastructure\Auth\OAuthIdentityProvider;
use OpenCFP\Infrastructure\Auth\SentryIdentityProvider;
use OpenCFP\Infrastructure\Crypto\PseudoRandomStringGenerator;
use OpenCFP\Infrastructure\OAuth\AccessTokenStorage;
use OpenCFP\Infrastructure\OAuth\ClientStorage;
use OpenCFP\Infrastructure\OAuth\ScopeStorage;
use OpenCFP\Infrastructure\OAuth\SessionStorage;
use OpenCFP\Infrastructure\Persistence\SpotSpeakerRepository;
use OpenCFP\Infrastructure\Persistence\SpotTalkRepository;
use RandomLib\Factory;
use Silex\Application;
use Silex\ServiceProviderInterface;

class ApplicationServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        $app['application.speakers'] = $app->share(function ($app) {
            $userMapper = $app['spot']->mapper(\OpenCFP\Domain\Entity\User::class);
            $talkMapper = $app['spot']->mapper(\OpenCFP\Domain\Entity\Talk::class);
            $speakerRepository = new SpotSpeakerRepository($userMapper);

            return new Speakers(
                new CallForProposal(new \DateTime($app->config('application.enddate'))),
                new SentryIdentityProvider($app['sentry'], $speakerRepository),
                $speakerRepository,
                new SpotTalkRepository($talkMapper),
                new EventDispatcher()
            );
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
            $userMapper = $app['spot']->mapper(\OpenCFP\Domain\Entity\User::class);
            $talkMapper = $app['spot']->mapper(\OpenCFP\Domain\Entity\Talk::class);
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
