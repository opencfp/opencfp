<?php

namespace OpenCFP\Provider\Gateways;

use Silex\Application;
use Silex\ControllerCollection;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class ApiGatewayProvider implements ServiceProviderInterface
{

    public function register(Application $app)
    {
        /* @var $api ControllerCollection */
        $api = $app['controllers_factory'];

        $api->before([$this, 'cleanRequest']);
        $api->before(function (Request $request) {
            $request->headers->set('Accept', 'application/json');

            if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
                $data = json_decode($request->getContent(), true);
                $request->request->replace(is_array($data) ? $data : array());
            }
        });

        if ($app->config('application.secure_ssl')) {
            $api->requireHttps();
        }

        $api->get('/me', 'controller.api.profile:handleShowSpeakerProfile');
        $api->get('/talks', 'controller.api.talk:handleViewAllTalks');
        $api->post('/talks', 'controller.api.talk:handleSubmitTalk');
        $api->get('/talks/{id}', 'controller.api.talk:handleViewTalk');

        $app->mount('/api', $api);
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