<?php

namespace OpenCFP\ServiceProvider;

use OpenCFP\Model\UserManager;
use OpenCFP\Service\AuthenticationService;
use OpenCFP\Service\CallForPaperService;
use OpenCFP\Service\ChangePasswordService;
use OpenCFP\Service\ProfileService;
use OpenCFP\Service\RegistrationService;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Cartalyst\Sentry\Facades\Native\Sentry as SentryFacade;
use Symfony\Component\HttpFoundation\Response;

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
        // Move the code in a service provider
        $app->error(function (\Exception $e, $code) use ($app) {
            $response = new Response();
            $response->setStatusCode($code);

            try {
                $response->setContent($app['twig']->render('error'.$code.'.twig'));
            } catch (\Exception $e) {
                $response->setStatusCode(500);
                $response->setContent($app['twig']->render('error.twig'));
            }

            return $response;
        });

        // Register some application specific services
        $app['user_manager'] = $app->share(function () use ($app) {
            return new UserManager($app['sentry.user_provider'], $app['sentry.group_provider']);
        });

        $app['cfp'] = $app->share(function () use ($app) {
            return new CallForPaperService($app['db'], $app['sentry'], $app['purifier']);
        });

        $app['profile'] = $app->share(function () use ($app) {
            return new ProfileService($app['db'], $app['sentry'], $app['purifier']);
        });

        $app['registration'] = $app->share(function () use ($app) {
            return new RegistrationService($app['db'], $app['user_manager'], $app['purifier']);
        });

        $app['change_password'] = $app->share(function () use ($app) {
            return new ChangePasswordService($app['db'], $app['sentry'], $app['purifier']);
        });

        $app['security'] = $app->share(function () use ($app) {
            return new AuthenticationService($app['sentry']);
        });

        // Override the default Sentry services
        $app['sentry'] = $app->extend('sentry', function ($sentry, $app) {
            SentryFacade::setupDatabaseResolver($app['db']);
            return $sentry;
        });

        $app['sentry.user_provider'] = $app->extend('sentry.user_provider', function ($userProvider, $app) {
            SentryFacade::setupDatabaseResolver($app['db']);
            return $userProvider;
        });

        $app['sentry.group_provider'] = $app->extend('sentry.group_provider', function ($groupProvider, $app) {
            SentryFacade::setupDatabaseResolver($app['db']);
            return $groupProvider;
        });

        // Override the default Twig service
        $app['twig'] = $app->extend('twig', function ($twig, $app) {
            $twig->addGlobal('site', array(
                'url'   => $app['application.url'],
                'title' => $app['application.title'],
            ));

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
        $app->get('/dashboard',                'OpenCFP\Controller\DashboardController::indexAction');
        $app->get('/talk/edit/{id}',           'OpenCFP\Controller\TalkController::editAction');
        $app->post('/talk/update/{id}',        'OpenCFP\Controller\TalkController::updateAction');
        $app->get('/talk/create',              'OpenCFP\Controller\TalkController::newAction');
        $app->post('/talk/create',             'OpenCFP\Controller\TalkController::createAction');
        $app->post('/talk/delete',             'OpenCFP\Controller\TalkController::deleteAction');
        $app->get('/login',                    'OpenCFP\Controller\SecurityController::loginAction');
        $app->post('/login',                   'OpenCFP\Controller\SecurityController::signinAction');
        $app->get('/logout',                   'OpenCFP\Controller\SecurityController::logoutAction');
        $app->get('/signup',                   'OpenCFP\Controller\SignupController::indexAction');
        $app->post('/signup',                  'OpenCFP\Controller\SignupController::processAction');
        $app->get('/signup/success',           'OpenCFP\Controller\SignupController::successAction');
        $app->get('/profile/edit',             'OpenCFP\Controller\ProfileController::editAction');
        $app->post('/profile/edit',            'OpenCFP\Controller\ProfileController::updateAction');
        $app->get('/profile/change_password',  'OpenCFP\Controller\ProfileController::passwordAction');
        $app->post('/profile/change_password', 'OpenCFP\Controller\ProfileController::passwordProcessAction');
    }

    public function boot(Application $app)
    {
    }
}
