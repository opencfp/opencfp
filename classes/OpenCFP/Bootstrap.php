<?php

namespace OpenCFP;

use OpenCFP\Config\ConfigINIFileLoader;
use OpenCFP\Config\ParameterResolver;
use OpenCFP\ServiceProvider\ApplicationServiceProvider;
use OpenCFP\ServiceProvider\DatabaseServiceProvider;
use OpenCFP\ServiceProvider\HtmlPurifierServiceProvider;
use OpenCFP\ServiceProvider\SentryServiceProvider;
use Silex\Application;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\SwiftmailerServiceProvider;
use Silex\Provider\TwigServiceProvider;

class Bootstrap
{
    private $_app;
    private $_booted;
    private $_debug;

    /**
     * Constructor.
     *
     * @param bool $debug Whether or not debug mode is enabled
     */
    public function __construct($debug = true)
    {
        $this->_booted = false;
        $this->_debug  = (boolean) $debug;
    }

    /**
     * Boots the application.
     *
     */
    private function bootstrap()
    {
        $this->initializeAutoLoader();

        // Create the new Silex application instance
        $this->_app = $this->createApplication();
        $this->_app['app.dir'] = $this->getAppDir();
        $this->_app['app.cache_dir'] = $this->getCacheDir();

        // Register a bunch of Silex extensions
        $this->_app->register(new DatabaseServiceProvider());
        $this->_app->register(new HtmlPurifierServiceProvider());
        $this->_app->register(new SessionServiceProvider());
        $this->_app->register(new TwigServiceProvider());
        $this->_app->register(new SwiftmailerServiceProvider());
        $this->_app->register(new SentryServiceProvider());

        // Load the application configuration settings
        $this->loadApplicationConfiguration();

        // Customize the application and register routes
        $this->_app->register(new ApplicationServiceProvider());

        $this->_booted = true;
    }

    /**
     * Loads the application configuration.
     *
     */
    private function loadApplicationConfiguration()
    {
        $application = $this->getApplication();

        $loader = new ConfigINIFileLoader($application, new ParameterResolver($application));
        $loader->load($this->getAppDir() . '/config/config.ini');
    }

    /**
     * Creates a new Silex application.
     *
     * @return \Silex\Application
     */
    protected function createApplication()
    {
        $app = new Application();
        $app['debug'] = $this->isDebug();

        return $app;
    }

    /**
     * Returns the Silex application.
     *
     * @return \Silex\Application
     */
    public function getApplication()
    {
        return $this->_app;
    }

    /**
     * Returns the absolute path to the application directory.
     *
     * @return string
     */
    public function getAppDir()
    {
        return dirname(dirname(__DIR__));
    }

    /**
     * Returns the absolute path to the application cache directory.
     *
     * @return string
     */
    public function getCacheDir()
    {
        return $this->getAppDir().'/cache';
    }

    /**
     * Returns whether or not debug mode is enabled.
     *
     * @return bool
     */
    public function isDebug()
    {
        return $this->_debug;
    }

    /**
     * Returns whether or not the application is booted.
     *
     * @return bool
     */
    public function isBooted()
    {
        return $this->_booted;
    }

    /**
     * Runs the application.
     *
     * @return string
     */
    public function runApplication()
    {
        if (!$this->isBooted()) {
            $this->bootstrap();
        }

        return $this->_app->run();
    }

    /**
     * Initializes the autoloaders.
     *
     * @throws \RuntimeException
     * @return The Composer autoloader
     */
    private function initializeAutoLoader()
    {
        $autoloader = $this->getAppDir() . '/vendor/autoload.php';
        if (!file_exists($autoloader)) {
            throw new \RuntimeException('Autoload file does not exist.  Did you run composer install?');
        }

        $loader = require $autoloader;

        return $loader;
    }
}
