<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2019 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP;

final class Path implements PathInterface
{
    /**
     * @var string
     */
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function basePath(): string
    {
        return $this->path;
    }

    public function uploadPath(): string
    {
        return $this->path . '/web/uploads';
    }

    public function assetsPath(): string
    {
        return $this->path . '/web/assets';
    }
}
