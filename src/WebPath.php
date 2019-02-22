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

final class WebPath implements PathInterface
{
    public function basePath(): string
    {
        return '/';
    }

    public function uploadPath(): string
    {
        return '/uploads/';
    }

    public function assetsPath(): string
    {
        return '/assets/';
    }
}
