<?php

namespace OpenCFP\Provider\Gateways;

use Cartalyst\Sentry\Sentry;
use Pimple\Container;
use Silex\Api\BootableProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;
use Pimple\ServiceProviderInterface;
use Spot\Locator;
use Symfony\Component\HttpFoundation\Request;

class OAuthGatewayProvider implements ServiceProviderInterface, BootableProviderInterface
{
    public function register(Container $app)
    {
        $app['controller.oauth.authorization'] = function ($app) {
            $server = new AuthorizationServer();

            $server->setSessionStorage(new SessionStorage());
            $server->setAccessTokenStorage(new AccessTokenStorage());
            $server->setRefreshTokenStorage(new RefreshTokenStorage());
            $server->setClientStorage(new ClientStorage());
            $server->setScopeStorage(new ScopeStorage());
            $server->setAuthCodeStorage(new AuthCodeStorage());

            $server->addGrantType(new AuthCodeGrant);
            $server->addGrantType(new RefreshTokenGrant);

            /* @var Locator $spot */
            $spot = $app['spot'];
            
            $userMapper = $spot->mapper(\OpenCFP\Domain\Entity\User::class);
            $speakerRepository = new SpotSpeakerRepository($userMapper);

            /* @var Sentry $sentry */
            $sentry = $app['sentry'];
            
            $controller = new AuthorizationController($server, new SentryIdentityProvider($sentry, $speakerRepository));
            $controller->setApplication($app);

            return $controller;
        };

        $app['controller.oauth.clients'] = function ($app) {
            /* @var Locator $spot */
            $spot = $app['spot'];
            
            return new ClientRegistrationController(
            $spot->mapper(\OpenCFP\Domain\OAuth\Client::class),
            $app['spot']->mapper(\OpenCFP\Domain\OAuth\Endpoint::class),
            $app['security.random']
            );
        };
    }

    public function boot(Application $app)
    {
        if (!$app->config('api.enabled')) {
            return;
        }

        /* @var $oauth ControllerCollection */
        $oauth = $app['controllers_factory'];

        $oauth->before(new RequestCleaner($app['purifier']));
        $oauth->before(function (Request $request, Application $app) {
            $request->headers->set('Accept', 'application/json');

            if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
                $data = json_decode($request->getContent(), true);
                $request->request->replace(is_array($data) ? $data : []);
            }
        });

        if ($app->config('application.secure_ssl')) {
            $oauth->requireHttps();
        }

        $oauth->get('/authorize', 'controller.oauth.authorization:authorize');
        $oauth->post('/authorize', 'controller.oauth.authorization:issueAuthCode');
        $oauth->post('/access_token', 'controller.oauth.authorization:issueAccessToken');
        $oauth->post('/clients', 'controller.oauth.clients:registerClient');

        $app->mount('/oauth', $oauth);
    }
}
