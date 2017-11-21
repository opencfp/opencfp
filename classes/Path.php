<?php

namespace OpenCFP;

class Path
{
    private $path;
    /**
     * @var Environment
     */
    private $env;

    public function __construct($path, Environment $env)
    {
        $this->path = $path;
        $this->env  = $env;
    }

    public function basePath()
    {
        return $this->path;
    }

    /**
     * Get the configuration path.
     *
     * @return string
     */
    public function configPath(): string
    {
        return $this->basePath() . "/config/{$this->env}.yml";
    }

    /**
     * Get the uploads path.
     *
     * The production path is separated to make it easier for prod environments where it is
     * not possible to store files on the disk.
     *
     * @return string
     */
    public function uploadPath(): string
    {
        if ($this->env->isProduction()) {
            return $this->basePath() . '/web/uploads';
        }

        return $this->basePath() . '/web/uploads';
    }

    /**
     * Get the templates path.
     *
     * @return string
     */
    public function templatesPath(): string
    {
        return $this->basePath() . '/resources/views';
    }

    /**
     * Get the public path.
     *
     * @return string
     */
    public function publicPath(): string
    {
        return $this->basePath() . '/web';
    }

    /**
     * Get the assets path.
     *
     * @return string
     */
    public function assetsPath(): string
    {
        return $this->basePath() . '/web/assets';
    }

    /**
     * Get the Twig cache path.
     *
     * @return string
     */
    public function cacheTwigPath(): string
    {
        return $this->basePath() . '/cache/twig';
    }

    /**
     * Get the HTML Purifier cache path.
     *
     * @return string
     */
    public function cachePurifierPath(): string
    {
        return $this->basePath() . '/cache/htmlpurifier';
    }
}
