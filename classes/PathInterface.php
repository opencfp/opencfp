<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP;

interface PathInterface
{
    public function basePath(): string;

    public function configPath(): string;

    public function uploadToPath(): string;

    public function downloadFromPath(): string;

    public function templatesPath(): string;

    public function publicPath(): string;

    public function assetsPath(): string;

    public function cacheTwigPath(): string;

    public function cachePurifierPath(): string;
}
