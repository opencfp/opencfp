<?php
namespace OpenCFP;

use Illuminate\Database\Capsule\Manager as Capsule;
use Swift_Mailer;
use Swift_SmtpTransport;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use OpenCFP\Config\ConfigINIFileLoader;
use Pimple;
use Twig_Environment;
use Twig_Loader_Filesystem;

$environment = isset($_SERVER['CFP_ENV']) ? $_SERVER['CFP_ENV'] : 'development';
// $environment = isset($_SERVER['CFP_ENV']) ? $_SERVER['CFP_ENV'] : 'production';
// Set constant for app wide use
define('APP_ENV', $environment);
define('APP_DIR', dirname(dirname(__DIR__)));

class Bootstrap
{
    private $_app;
    private $_config;
    private $_twig;
    private $_purifier;

    function __construct(array $config = null)
    {
        $this->_config = $config;
        $this->initializeAutoLoader();
        $this->_app = $this->getApp();
    }

    function getApp()
    {
        if (isset($this->_app)) {
            return $this->_app;
        }

        // Initialize out Silex app and let's do it
        $app = new \Silex\Application();

        $app['config'] = $this->getConfigContainer();

        if ($this->getConfig('twig.debug')) {
            $app['debug'] = $this->getConfig('twig.debug');
        }

        // Register our session provider
        $app->register(new \Silex\Provider\SessionServiceProvider());
        $app->before(function ($request) use ($app) {
            $app['session']->start();
        });

        $app['url'] = $this->getConfig('application.url') . $this->getPort();
        $app['uploadPath'] = $this->getConfig('upload.path');
        $app['confAirport'] = $this->getConfig('application.airport');
        $app['arrival'] = $this->getConfig('application.arrival');
        $app['departure'] = $this->getConfig('application.departure');

        // Register the Twig provider and lazy-load the global values
        $app->register(
            new \Silex\Provider\TwigServiceProvider(),
            array(
                'twig.path' => APP_DIR . $this->getConfig('twig.template_dir'),
                'twig.options' => array(
                    'cache' => $this->getConfig('cache.enabled') ? $this->getTwigCacheDirectory() : false
                )
            )
        );
        $that = $this;
        $app['twig'] = $app->share($app->extend('twig', function ($twig, $app) use ($that) {
            $twig->addGlobal('site', array(
                'url' => $app['url'],
                'title' => $that->getConfig('application.title'),
                'email' => $that->getConfig('application.email'),
                'eventurl' => $that->getConfig('application.eventurl'),
                'enddate' => $that->getConfig('application.enddate')
            ));

            return $twig;
        }));

        // Register our use of the Form Service Provider
        $app->register(new \Silex\Provider\FormServiceProvider());
        $app->register(new \Silex\Provider\ValidatorServiceProvider());
        $app->register(new \Silex\Provider\TranslationServiceProvider(), array(
            'translator.messages' => array()
        ));

        $app['db'] = $this->getDb();
        $app['spot'] = $this->getSpot();
        $app['purifier'] = $this->getPurifier();
        $app['mailer'] = $this->getSwiftMailer();

        // We're using Sentry, so make it available to app
        $app['sentry'] = $app->share(function() use ($app) {
            $hasher = new \Cartalyst\Sentry\Hashing\NativeHasher;
            $userProvider = new \Cartalyst\Sentry\Users\Eloquent\Provider($hasher);
            $groupProvider = new \Cartalyst\Sentry\Groups\Eloquent\Provider;
            $throttleProvider = new \Cartalyst\Sentry\Throttling\Eloquent\Provider($userProvider);
            $session = new \OpenCFP\SymfonySentrySession($app['session']);
            $cookie = new \Cartalyst\Sentry\Cookies\NativeCookie(array());

            $sentry = new \Cartalyst\Sentry\Sentry(
                $userProvider,
                $groupProvider,
                $throttleProvider,
                $session,
                $cookie
            );

            \Cartalyst\Sentry\Facades\Native\Sentry::setupDatabaseResolver($app['db']);
            $throttleProvider->disable();
            return $sentry;
        });

        $app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
            if ($app['sentry']->check()) {
                $twig->addGlobal('user', $app['sentry']->getUser());
            }

            return $twig;
        }));

        // Configure our flash messages functionality
        $app->before(function() use ($app) {
            $flash = $app['session']->get('flash');
            $app['session']->set('flash', null);

            if (!empty($flash)) {
                $app['twig']->addGlobal('flash', $flash);
            }
        });

        // Add current page global
        $app->before(function (Request $request) use ($app) {
            $app['twig']->addGlobal('current_page', $request->getRequestUri());
        });

        // Define error template paths
        if (!$app['debug']) {
            $app->error(function (\Exception $e, $code) use ($app) {
                switch ($code) {
                    case 401:
                        $message = $app['twig']->render('error/401.twig');
                        break;
                    case 403:
                        $message = $app['twig']->render('error/403.twig');
                        break;
                    case 404:
                        $message = $app['twig']->render('error/404.twig');
                        break;
                    default:
                        $message = $app['twig']->render('error/500.twig');
                }
                return new Response($message, $code);
            });
        }

        $app = $this->defineRoutes($app);

        // Add the starting date for submissions
        $app['cfpdate'] = $this->getConfig('application.cfpdate');

        return $app;
    }

    protected function defineRoutes($app)
    {
        // Load Static Routes
        $app->get('/', function() use($app) {
            $view = array();
            if ($app['sentry']->check()) {
                $user = $app['sentry']->getUser();
                $view['user'] = $user;
                $view['permissions']['admin'] = $user->hasPermission('admin');
            }

            $template = $app['twig']->loadTemplate('home.twig');
            return $template->render($view);
        });

        // Secondary Pages
        $app->get('/package', 'OpenCFP\Controller\DashboardController::packageAction');
        $app->get('/ideas', 'OpenCFP\Controller\DashboardController::ideasAction');

        // User Dashboard
        $app->get('/dashboard', 'OpenCFP\Controller\DashboardController::indexAction');

        // Talks
        $app->get('/talk/edit/{id}', 'OpenCFP\Controller\TalkController::editAction');
        $app->get('/talk/create', 'OpenCFP\Controller\TalkController::createAction');
        $app->post('/talk/create', 'OpenCFP\Controller\TalkController::processCreateAction');
        $app->post('/talk/update', 'OpenCFP\Controller\TalkController::updateAction');
        $app->post('/talk/delete', 'OpenCFP\Controller\TalkController::deleteAction');
        $app->get('/talk/{id}', 'OpenCFP\Controller\TalkController::viewAction');

        // Login/Logout
        $app->get('/login', 'OpenCFP\Controller\SecurityController::indexAction');
        $app->post('/login', 'OpenCFP\Controller\SecurityController::processAction');
        $app->get('/logout', 'OpenCFP\Controller\SecurityController::outAction');

        // Create Account
        $app->get('/signup', 'OpenCFP\Controller\SignupController::indexAction');
        $app->post('/signup', 'OpenCFP\Controller\SignupController::processAction');
        $app->get('/signup/success', 'OpenCFP\Controller\SignupController::successAction');

        // Edit Profile/Account
        $app->get('/profile/edit/{id}', 'OpenCFP\Controller\ProfileController::editAction');
        $app->post('/profile/edit', 'OpenCFP\Controller\ProfileController::processAction');

        // Change/forgot Password
        $app->get('/profile/change_password', 'OpenCFP\Controller\ProfileController::passwordAction');
        $app->post('/profile/change_password', 'OpenCFP\Controller\ProfileController::passwordProcessAction');
        $app->get('/forgot', 'OpenCFP\Controller\ForgotController::indexAction');
        $app->post('/forgot', 'OpenCFP\Controller\ForgotController::sendResetAction');
        $app->get('/forgot_success', 'OpenCFP\Controller\ForgotController::successAction');
        $app->post('/reset', 'OpenCFP\Controller\ForgotController::resetAction');
        $app->get('/reset/{user_id}/{reset_code}', 'OpenCFP\Controller\ForgotController::processResetAction');
        $app->post('/updatepassword', 'OpenCFP\Controller\ForgotController::updatePasswordAction');

        // Admin Routes
        $app->get('/admin', 'OpenCFP\Controller\Admin\DashboardController::indexAction');

        // Admin::Talks
        $app->get('/admin/talks', 'OpenCFP\Controller\Admin\TalksController::indexAction');
        $app->get('/admin/talks/{id}', 'OpenCFP\Controller\Admin\TalksController::viewAction');
        $app->post('/admin/talks/{id}/favorite', 'OpenCFP\Controller\Admin\TalksController::favoriteAction');
        $app->post('/admin/talks/{id}/select', 'OpenCFP\Controller\Admin\TalksController::selectAction');

        // Admin::Speakers
        $app->get('/admin/speakers', 'OpenCFP\Controller\Admin\SpeakersController::indexAction');
        $app->get('/admin/speakers/{id}', 'OpenCFP\Controller\Admin\SpeakersController::viewAction');
        $app->get('/admin/speakers/delete/{id}', 'OpenCFP\Controller\Admin\SpeakersController::deleteAction');
        $app->get('/admin/admins', 'OpenCFP\Controller\Admin\AdminsController::indexAction');
        $app->get('/admin/admins/{id}', 'OpenCFP\Controller\Admin\AdminsController::removeAction');

        // Admin::Review
        $app->get('/admin/review', 'OpenCFP\Controller\Admin\ReviewController::indexAction');

        return $app;
    }

    /**
     * @param string $configKey
     * @return mixed
     */
    public function getConfig($configKey)
    {
        $config = $this->getConfigContainer();

        if (!isset($config[$configKey])) {
            return null;
        }

        return $this->_config[$configKey];
    }

    public function getConfigContainer()
    {
        if (isset($this->_config)) {
            return $this->_config;
        }

        $loader = new ConfigINIFileLoader($this->getConfigPath());
        $configData = $loader->load();

        // Place our info into Pimple
        $this->_config = new \Pimple();

        foreach ($configData as $category => $info) {
            foreach ($info as $key => $value) {
                $this->_config["{$category}.{$key}"] = $value;
            }
        }

        return $this->_config;
    }

    public function getConfigPath()
    {
        return APP_DIR . "/config/config." . APP_ENV . ".ini";
    }

    private function getTwigCacheDirectory()
    {
        return APP_DIR . $this->getCacheDirectory() . '/twig';
    }

    private function getPurifierCacheDirectory()
    {
        return APP_DIR . $this->getCacheDirectory() . '/htmlpurifier';
    }

    private function getCacheDirectory()
    {
        return $this->getConfig('cache.directory') ?: '/cache';
    }

    public function getTwig()
    {
        if (!isset($this->_twig)) {
            // Initialize Twig
            $loader = new Twig_Loader_Filesystem(APP_DIR . $this->getConfig('twig.template_dir'));
            $this->_twig = new Twig_Environment($loader);
            $this->_twig->addGlobal('site', array(
                'url' => $this->getConfig('application.url'),
                'title' => $this->getConfig('application.title'),
                'email' => $this->getConfig('application.email'),
                'eventurl' => $this->getConfig('application.eventurl'),
                'enddate' => $this->getConfig('application.enddate')
            ));
        }
        return $this->_twig;
    }

    public function getPurifier() {
        if (!isset($this->_purifier)) {
            $config = \HTMLPurifier_Config::createDefault();

            if ($this->getConfig('cache.enabled')) {
                if (!is_dir($this->getPurifierCacheDirectory())) {
                    mkdir($this->getPurifierCacheDirectory(), 0755, true);
                }
                $config->set('Cache.SerializerPath', $this->getPurifierCacheDirectory());
            }

            $this->_purifier = new \HTMLPurifier($config);
        }

        return $this->_purifier;
    }

    /**
     * @return \PDO
     */
    public function getDb()
    {
        $container = $this->getConfigContainer();
        return new \PDO(
            $container['database.dsn'],
            $container['database.user'],
            $container['database.password']
        );
    }

    public function getSpot()
    {
        $cfg = new \Spot\Config();
        $cfg->addConnection('mysql', [
            'dbname' => $this->getConfig('database.database'),
            'user' => $this->getConfig('database.user'),
            'password' => $this->getConfig('database.password'),
            'host' => $this->getConfig('database.host'),
            'driver' => 'pdo_mysql'
        ]);
        return new \Spot\Locator($cfg);
    }

    private function initializeAutoLoader()
    {
        if (!file_exists(APP_DIR . '/vendor/autoload.php')) {
            throw new \Exception('Autoload file does not exist.  Did you run composer install?');
        }

        require APP_DIR . '/vendor/autoload.php';
    }

    private function getSwiftMailer()
    {
        // Create our Mailer object
        $transport = new Swift_SmtpTransport(
            $this->getConfig('smtp.host'),
            $this->getConfig('smtp.port')
        );

        if ($this->getConfig('smtp.user') !== '') {
            $transport->setUsername($this->getConfig('smtp.user'))
            ->setPassword($this->getConfig('smtp.password'));
        }

        if ($this->getConfig('smtp.encryption') !== '') {
            $transport->setEncryption($this->getConfig('smtp.encryption'));
        }

        return new Swift_Mailer($transport);
    }

    /**
     * @return string
     */
    private function getPort()
    {
        if (isset($_SERVER['SERVER_PORT']) && !in_array($_SERVER['SERVER_PORT'], array(80, 443))) {
            return ':' . $_SERVER['SERVER_PORT'];
        }

        return '';
    }

}
