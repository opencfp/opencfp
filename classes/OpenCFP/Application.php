<?php

namespace OpenCFP;

use Silex\Application as SilexApplication;
use Igorw\Silex\ConfigServiceProvider;

final class Application extends SilexApplication
{

    public function __construct($basePath, Environment $environment)
    {
        parent::__construct();

        $this['path'] = $basePath;
        $this['env'] = $environment;

        $this->bindPathsInApplicationContainer();
        $this->bindConfiguration();
    }

    /**
     * Puts various paths into the application container.
     */
    protected function bindPathsInApplicationContainer()
    {
        foreach (['config', 'upload'] as $slug) {
            $this["paths.{$slug}"] = $this->{$slug . 'Path'}();
        }
    }

    /**
     * Loads configuration and puts application in debug-mode if not in production environment.
     */
    private function bindConfiguration()
    {
        $this->register(new ConfigServiceProvider($this->configPath(), [], null, 'config'));

        if ( ! $this->isProduction()) {
            $this['debug'] = true;
        }
    }

    /**
     * Get the base path for the application.
     * @return string
     */
    public function basePath()
    {
        return $this['path'];
    }

    /**
     * Get the configuration path.
     * @return string
     */
    public function configPath()
    {
        return $this->basePath() . "/config/{$this['env']}.yml";
    }

    /**
     * Get the uploads path.
     * @return string
     */
    public function uploadPath()
    {
        return $this->basePath() . "/web/uploads";
    }

    /**
     * Get the templates path.
     * @return string
     */
    public function templatesPath()
    {
        return $this->basePath() . "/templates";
    }

    /**
     * Tells if application is in production environment.
     * @return boolean
     */
    public function isProduction()
    {
        return $this['env']->equals(Environment::production());
    }

    /**
     * Tells if application is in development environment.
     * @return boolean
     */
    public function isDevelopment()
    {
        return $this['env']->equals(Environment::development());
    }

    /**
     * Tells if application is in testing environment.
     * @return boolean
     */
    public function isTesting()
    {
        return $this['env']->equals(Environment::testing());
    }
}