<?php

namespace OpenCFP\ServiceProvider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Cartalyst\Sentry\Facades\Native\Sentry as SentryFacade;

/**
 * Application customization.
 *
 * @author Hugo Hamon <hugo.hamon@sensiolabs.com>
 */
class ApplicationServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        // Register exception handler
        // Move the code in a Service provider
        $app->error(function (\Exception $e, $code) use ($app) {
            if ($app['debug']) {

                echo $e->getMessage();
                die;
            }
        });

        // Override the default Sentry service
        $app['sentry'] = $app->extend('sentry', function($sentry, $app) {
            SentryFacade::setupDatabaseResolver($app['db']);
            return $sentry;
        });

        // Override the default Twig service
        $app['twig'] = $app->extend('twig', function($twig, $app) {
            $twig->addGlobal('app', array(
                'session' => $app['session'],
                'request' => $app['request'],
                'user'    => $app['sentry']->getUser(),
            ));

            return $twig;
        });

        // Register routes
        $app->get('/', function() use($app) {
            return $app['twig']->render('home.twig');
        });

        // @todo refactor to a ControllerServiceProvider implementation
        $app->get('/dashboard', 'OpenCFP\DashboardController::indexAction');
        $app->get('/talk/edit/{id}', 'OpenCFP\TalkController::editAction');
        $app->get('/talk/create', 'OpenCFP\TalkController::createAction');
        $app->post('/talk/create', 'OpenCFP\TalkController::processCreateAction');
        $app->post('/talk/update', 'OpenCFP\TalkController::updateAction');
        $app->post('/talk/delete', 'OpenCFP\TalkController::deleteAction');
        $app->get('/login', 'OpenCFP\LoginController::indexAction');
        $app->post('/login', 'OpenCFP\LoginController::processAction');
        $app->get('/logout', 'OpenCFP\LoginController::outAction');
        $app->get('/signup', 'OpenCFP\SignupController::indexAction');
        $app->post('/signup', 'OpenCFP\SignupController::processAction');
        $app->get('/signup/success', 'OpenCFP\SignupController::successAction');
        $app->get('/profile/edit/{id}', 'OpenCFP\ProfileController::editAction');
        $app->post('/profile/edit', 'OpenCFP\ProfileController::processAction');
        $app->get('/profile/change_password', 'OpenCFP\ProfileController::passwordAction');
        $app->post('/profile/change_password', 'OpenCFP\ProfileController::passwordProcessAction');
    }

    public function boot(Application $app)
    {
    }
}
