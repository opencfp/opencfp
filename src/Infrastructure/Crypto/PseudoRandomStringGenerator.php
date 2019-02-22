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

namespace OpenCFP\Infrastructure\Crypto;

use OpenCFP\Domain\Services\RandomStringGenerator;

class PseudoRandomStringGenerator implements RandomStringGenerator
{
    public function generate($length = 40)
    {
        return \substr(\bin2hex(\random_bytes($length)), 0, $length);
    }
}
