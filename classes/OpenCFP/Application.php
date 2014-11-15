<?php

namespace OpenCFP;

use Silex\Application as SilexApplication;

final class Application extends SilexApplication
{

    public function __construct($basePath, Environment $environment)
    {
        parent::__construct();

        $this['path'] = $basePath;
        $this['env'] = (string)$environment;

        $this->bindPathsInApplicationContainer();
    }

    /**
     * Puts various paths into the application container.
     */
    protected function bindPathsInApplicationContainer()
    {
        foreach (['config'] as $slug) {
            $this["paths.{$slug}"] = $this->{$slug . 'Path'}();
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
     * Get the base configuration path.
     * @return string
     */
    public function configPath()
    {
        return $this->basePath() . "/config/{$this['env']}.yml";
    }

    public function environment()
    {
        return $this['env'];
    }
}