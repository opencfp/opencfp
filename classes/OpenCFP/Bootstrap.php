<?php
namespace OpenCFP;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Pimple;
use Twig_Environment;
use Twig_Loader_Filesystem;

class Bootstrap
{
    private $_app;
    private $_config;
    private $_twig;

    function __construct($initializer)
    {
        $initializer($this->getApp());
    }

    function getApp()
    {
        if (!isset($this->_app)) {
            // Initialize out Silex app and let's do it
            $app = new \Silex\Application();

            // Register our session provider
            $app->register(new \Silex\Provider\SessionServiceProvider());
            $app['session']->start();

            // Register the Twig provider
            $app->register(new \Silex\Provider\TwigServiceProvider());
            $app['twig'] = $this->getTwig();

            $app['db'] = $this->getDb();

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

            $this->_app = $app;
        }
        return $this->_app;
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
        return $configKey ? $this->_config[$configKey] : $this->_config;
    }

    public function getTwig()
    {
        if (!isset($this->_twig)) {
            // Initialize Twig
            $loader = new Twig_Loader_Filesystem(APP_DIR . $this->getConfig('twig.template_dir'));
            $this->_twig = new Twig_Environment($loader);
        }
        return $this->_twig;
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
}