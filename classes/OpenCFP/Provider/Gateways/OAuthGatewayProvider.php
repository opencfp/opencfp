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
        if (!$app->config('api.enabled')) {
            return;
        }

        /* @var $oauth ControllerCollection */
        $oauth = $app['controllers_factory'];

        $oauth->before([$this, 'cleanRequest']);
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

    public function cleanRequest(Request $request, Application $app)
    {
        $request->query->replace($this->clean($request->query->all(), $app['purifier']));
        $request->request->replace($this->clean($request->request->all(), $app['purifier']));
    }

    public function clean(array $data, \HTMLPurifier $purifier)
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->clean($value, $purifier);
            } else {
                $sanitized[$key] = $purifier->purify($value);;
            }
        }

        return $sanitized;
    }

    public function boot(Application $app)
    {
    }
}