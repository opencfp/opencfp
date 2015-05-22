<?php

namespace OpenCFP\Provider\Gateways;

use Silex\Application;
use Silex\ControllerCollection;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class OAuthGatewayProvider implements ServiceProviderInterface
{

    public function register(Application $app)
    {
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
                $request->request->replace(is_array($data) ? $data : array());
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