<?php

namespace OpenCFP;

final class Path
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var Environment
     */
    private $env;

    public function __construct(string $path, Environment $env)
    {
        $this->path = $path;
        $this->env  = $env;
    }

    public function basePath(): string
    {
        return $this->path;
    }

    public function configPath(): string
    {
        return $this->basePath() . "/config/{$this->env}.yml";
    }

    public function uploadPath(): string
    {
        return $this->basePath() . '/web/uploads';
    }

    public function templatesPath(): string
    {
        return $this->basePath() . '/resources/views';
    }

    public function publicPath(): string
    {
        return $this->basePath() . '/web';
    }

    public function assetsPath(): string
    {
        return $this->basePath() . '/web/assets';
    }

    public function cacheTwigPath(): string
    {
        return $this->basePath() . '/cache/twig';
    }

    public function cachePurifierPath(): string
    {
        return $this->basePath() . '/cache/htmlpurifier';
    }
}
