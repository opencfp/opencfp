<?php namespace OpenCFP\Http\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityController extends BaseCon
{
    use FlashableTrait;

    public function indexAction(Request $req, Application $app)
    {
        $template = $app['twig']->loadTemplate('login.twig');

        return $template->render(array());
    }

    public function processAction(Request $req, Application $app)
    {
        $template = $app['twig']->loadTemplate('login.twig');
        $template_data = array();
        $code = 200;

        try {
            $page = new \OpenCFP\Login($app['sentry']);

            if ($page->authenticate($req->get('email'), $req->get('password'))) {
                return $app->redirect($app->url('dashboard'));
            }

            $errorMessage = $page->getAuthenticationMessage();

            $template_data = array(
                'email' => $req->get('email'),
            );
            $code = 400;
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            $template_data = array(
                'email' => $req->get('email'),
            );
            $code = 400;
        }

        // Set Success Flash Message
        $app['session']->set('flash', array(
            'type' => 'error',
            'short' => 'Error',
            'ext' => $errorMessage,
        ));

        $template_data['flash'] = $this->getFlash($app);

        return new Response($template->render($template_data), $code);
    }

    public function outAction(Request $req, Application $app)
    {
        $app['sentry']->logout();

        return $app->redirect('/');
    }
}
