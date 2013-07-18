<?php
namespace OpenCFP;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use OpenCFP\Config\ConfigINIFileLoader;
use Pimple;
use Twig_Environment;
use Twig_Loader_Filesystem;

class Bootstrap
{
    private $_app;
    private $_config;
    private $_twig;
    private $_purifier;

    function __construct()
    {
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

        $app['debug'] = true;
        // Register our session provider
        $app->register(new \Silex\Provider\SessionServiceProvider());
        $app['session']->start();
		$app['url'] = $this->getConfig('application.url');
        // Register the Twig provider
        $app->register(new \Silex\Provider\TwigServiceProvider());
        $app['twig'] = $this->getTwig();

        $app['db'] = $this->getDb();

        $app['purifier'] = $this->getPurifier();

        // We're using Sentry, so make it available to app
        $app['sentry'] = $app->share(function() use ($app) {
            $hasher = new \Cartalyst\Sentry\Hashing\NativeHasher;
            $userProvider = new \Cartalyst\Sentry\Users\Eloquent\Provider($hasher);
            $groupProvider = new \Cartalyst\Sentry\Groups\Eloquent\Provider;
            $throttleProvider = new \Cartalyst\Sentry\Throttling\Eloquent\Provider($userProvider);
            $session = new \Cartalyst\Sentry\Sessions\NativeSession;
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

        // Configure our flash messages functionality
        $app->before(function() use ($app) {
            $flash = $app['session']->get('flash');
            $app['session']->set('flash', null);

            if (!empty($flash)) {
                $app['twig']->addGlobal('flash', $flash);
            }
        });

        $app = $this->defineRoutes($app);

        return $app;
    }

    protected function defineRoutes($app)
    {
        $app->get('/', function() use($app) {
            $view = array();
            if ($app['sentry']->check()) {
                $view['user'] = $app['sentry']->getUser();
            }

            $template = $app['twig']->loadTemplate('home.twig');
            return $template->render($view);
        });

        $app->get('/dashboard', 'OpenCFP\Controller\DashboardController::indexAction');
        $app->get('/talk/edit/{id}', 'OpenCFP\Controller\TalkController::editAction');
        $app->get('/talk/create', 'OpenCFP\Controller\TalkController::createAction');
        $app->post('/talk/create', 'OpenCFP\Controller\TalkController::processCreateAction');
        $app->post('/talk/update', 'OpenCFP\Controller\TalkController::updateAction');
        $app->post('/talk/delete', 'OpenCFP\Controller\TalkController::deleteAction');
        $app->get('/login', 'OpenCFP\Controller\SecurityController::indexAction');
        $app->post('/login', 'OpenCFP\Controller\SecurityController::processAction');
        $app->get('/logout', 'OpenCFP\Controller\SecurityController::outAction');
        $app->get('/signup', 'OpenCFP\Controller\SignupController::indexAction');
        $app->post('/signup', 'OpenCFP\Controller\SignupController::processAction');
        $app->get('/signup/success', 'OpenCFP\Controller\SignupController::successAction');
        $app->get('/profile/edit/{id}', 'OpenCFP\Controller\ProfileController::editAction');
        $app->post('/profile/edit', 'OpenCFP\Controller\ProfileController::processAction');
        $app->get('/profile/change_password', 'OpenCFP\Controller\ProfileController::passwordAction');
        $app->post('/profile/change_password', 'OpenCFP\Controller\ProfileController::passwordProcessAction');

        return $app;
    }

    /**
     * @param bool $configKey
     * @return Pimple | string
     */
    public function getConfig($configKey = false)
    {
        if (!isset($this->_config)) {
            $loader = new ConfigINIFileLoader(APP_DIR . '/config/config.ini');
            $configData = $loader->load();

            // Place our info into Pimple
            $this->_config = new Pimple();

            foreach ($configData as $category => $info) {
                foreach ($info as $key => $value) {
                    $this->_config["{$category}.{$key}"] = $value;
                }
            }
        }

        if (!$configKey) {
            return $this->_config;
        }

        if (empty($this->_config[$configKey])) {
            return null;
        }

        return $this->_config[$configKey];
    }

    public function getTwig()
    {
        if (!isset($this->_twig)) {
            // Initialize Twig
            $loader = new Twig_Loader_Filesystem(APP_DIR . $this->getConfig('twig.template_dir'));
            $this->_twig = new Twig_Environment($loader);
            $this->_twig->addGlobal('site', array(
                'url' => $this->getConfig('application.url'),
                'title' => $this->getConfig('application.title')
            ));
        }
        return $this->_twig;
    }

    public function getPurifier() {
        if (!isset($this->_purifier)) {
            $config = \HTMLPurifier_Config::createDefault();
            if ($cachedir = $this->getConfig('htmlpurifier.cachedir')) {
                $config->set('Cache.SerializerPath', $cachedir);
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
        $container = $this->getConfig();
        return new \PDO(
            $container['database.dsn'],
            $container['database.user'],
            $container['database.password']
        );
    }

    private function initializeAutoLoader()
    {
        define('APP_DIR', dirname(dirname(__DIR__)));
        if (!file_exists(APP_DIR . '/vendor/autoload.php')) {
            throw new Exception('Autoload file does not exist.  Did you run composer install?');
        }

        require APP_DIR . '/vendor/autoload.php';
    }
}
