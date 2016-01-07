<?php

namespace OpenCFP\Provider\Gateways;

use Silex\Application;
use Silex\ControllerCollection;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class OAuthGatewayProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['controller.oauth.authorization'] = $app->share(function ($app) {
            $server = new AuthorizationServer();

            $server->setSessionStorage(new SessionStorage());
            $server->setAccessTokenStorage(new AccessTokenStorage());
            $server->setRefreshTokenStorage(new RefreshTokenStorage());
            $server->setClientStorage(new ClientStorage());
            $server->setScopeStorage(new ScopeStorage());
            $server->setAuthCodeStorage(new AuthCodeStorage());

            $server->addGrantType(new AuthCodeGrant);
            $server->addGrantType(new RefreshTokenGrant);

            $userMapper = $app['spot']->mapper(\OpenCFP\Domain\Entity\User::class);
            $speakerRepository = new SpotSpeakerRepository($userMapper);

            $controller = new AuthorizationController($server, new SentryIdentityProvider($app['sentry'], $speakerRepository));
            $controller->setApplication($app);

            return $controller;
        });

        $app['controller.oauth.clients'] = $app->share(function ($app) {
            return new ClientRegistrationController(
            $app['spot']->mapper(\OpenCFP\Domain\OAuth\Client::class),
            $app['spot']->mapper(\OpenCFP\Domain\OAuth\Endpoint::class),
            $app['security.random']
            );
        });
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
