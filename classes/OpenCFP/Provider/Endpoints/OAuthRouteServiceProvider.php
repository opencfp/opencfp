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
        /* @var $oauth ControllerCollection */
        $oauth = $app['controllers_factory'];

        ///////////////////////////////////////////////////////////////

        $oauth->get('/authorize', 'controller.oauth.authorization:authorize');
        $oauth->post('/access_token', 'controller.oauth.authorization:issueAccessToken');

        ///////////////////////////////////////////////////////////////

        $oauth->before(function(Request $request, Application $app) {
            foreach ($request->query as $key => $value) {
                $request->query->set($key, $app['purifier']->purify($value));
            }
            foreach ($request->request as $key => $value) {
                $request->query->set($key, $app['purifier']->purify($value));
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
