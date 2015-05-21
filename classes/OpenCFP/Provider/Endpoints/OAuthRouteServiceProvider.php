<?php namespace OpenCFP\Provider\Endpoints;

use Silex\Application;
use Silex\ControllerCollection;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class OAuthRouteServiceProvider  implements ServiceProviderInterface
{

    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        if (!$app->config('api.enabled')) {
            return;
        }

        /* @var $oauth ControllerCollection */
        $oauth = $app['controllers_factory'];

        ///////////////////////////////////////////////////////////////

        $oauth->get('/authorize', 'controller.oauth.authorization:authorize');
        $oauth->post('/authorize', 'controller.oauth.authorization:issueAuthCode');
        $oauth->post('/access_token', 'controller.oauth.authorization:issueAccessToken');
        $oauth->post('/clients', 'controller.oauth.clients:registerClient');

        $oauth->get('/callback', function (Request $request) {
            return new JsonResponse($request->query->all());
        });

        ///////////////////////////////////////////////////////////////

        $oauth->before(function(Request $request, Application $app) {
            foreach ($request->query as $key => $value) {
                $request->query->set($key, $app['purifier']->purify($value));
            }
            foreach ($request->request as $key => $value) {
                $request->request->set($key, $app['purifier']->purify($value));
            }

            if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
                $data = json_decode($request->getContent(), true);
                $request->request->replace(is_array($data) ? $data : array());
            }
        });

        if ($app->config('application.secure_ssl')) {
            $oauth->requireHttps();
        }

        $app->mount('/oauth', $oauth);
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
    }
}
