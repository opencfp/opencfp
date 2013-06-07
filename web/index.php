<?php
require '../bootstrap.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// Initialize out Silex app and let's do it
$app = new Silex\Application();

// Register our session provider
$app->register(new Silex\Provider\SessionServiceProvider());
$app['session']->start();

// Register the Twig provider
$app->register(new Silex\Provider\TwigServiceProvider());
$app['twig'] = $twig;

// Add our DB to the p
$app['db'] = new \PDO(
    $container['database.dsn'],
    $container['database.user'],
    $container['database.password']
); 

// We're using Sentry, so make it available to app
$app['sentry'] = $app->share(function() use ($app) {
    $hasher = new Cartalyst\Sentry\Hashing\NativeHasher;
    $userProvider = new Cartalyst\Sentry\Users\Eloquent\Provider($hasher);
    $groupProvider = new Cartalyst\Sentry\Groups\Eloquent\Provider;
    $throttleProvider = new Cartalyst\Sentry\Throttling\Eloquent\Provider($userProvider);
    $session = new Cartalyst\Sentry\Sessions\NativeSession;
    $cookie = new Cartalyst\Sentry\Cookies\NativeCookie(array());

    $sentry = new Cartalyst\Sentry\Sentry(
        $userProvider,
        $groupProvider,
        $throttleProvider,
        $session,
        $cookie
    );

    Cartalyst\Sentry\Facades\Native\Sentry::setupDatabaseResolver($app['db']);

    return $sentry;
});

// Configure our flash messages functionality
$app->before(function() use ($app) {
    $flash = $app['session']->get('flash');
    $app['session']->set('flash', null);

    if (!empty($flash)) {
        $app['twig']->addGlobal('flash', $flash);
    }
});

// Configure our routes
$app->get('/', function() use($app) {
    $template = $app['twig']->loadTemplate('home.twig');
    return $template->render(array());
});
$app->get('/dashboard', 'OpenCFP\DashboardController::indexAction');
$app->get('/talk/edit/{id}', 'OpenCFP\TalkController::editAction');
$app->post('/talk/create', 'OpenCFP\TalkController::createAction');
$app->get('/login', 'OpenCFP\LoginController::indexAction');
$app->post('/login', 'OpenCFP\LoginController::processAction');
$app->get('/signup', 'OpenCFP\SignupController::indexAction');
$app->post('/signup', 'OpenCFP\SignupController::processAction');

$app['debug'] = true;
$app->run();
