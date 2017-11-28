<?php

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

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
        return $this->path . "/config/{$this->env}.yml";
    }

    public function uploadPath(): string
    {
        return $this->path . '/web/uploads';
    }

    public function templatesPath(): string
    {
        return $this->path . '/resources/views';
    }

    public function publicPath(): string
    {
        return $this->path . '/web';
    }

    public function assetsPath(): string
    {
        return $this->path . '/web/assets';
    }

    public function cacheTwigPath(): string
    {
        return $this->path . '/cache/twig';
    }

    public function cachePurifierPath(): string
    {
        return $this->path . '/cache/htmlpurifier';
    }
}
