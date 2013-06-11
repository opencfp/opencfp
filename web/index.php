<?php

define('APP_DIR', dirname(__DIR__));
if (!file_exists(APP_DIR . '/vendor/autoload.php')) {
    throw new Exception('Autoload file does not exist.  Did you run composer install?');
}

require APP_DIR . '/vendor/autoload.php';

new \OpenCFP\Bootstrap(function (\Silex\Application $app) {
    $app->get('/', function() use($app) {
        $template = $app['twig']->loadTemplate('home.twig');
        return $template->render(array());
    });

    $app->get('/dashboard', 'OpenCFP\DashboardController::indexAction');
    $app->get('/talk/edit/{id}', 'OpenCFP\TalkController::editAction');
    $app->post('/talk/create', 'OpenCFP\TalkController::createAction');
    $app->get('/login', 'OpenCFP\LoginController::indexAction');
    $app->post('/login', 'OpenCFP\LoginController::processAction');
    $app->get('/logout', 'OpenCFP\LoginController::outAction');
    $app->get('/signup', 'OpenCFP\SignupController::indexAction');
    $app->post('/signup', 'OpenCFP\SignupController::processAction');

    $app['debug'] = true;
    $app->run();
});

