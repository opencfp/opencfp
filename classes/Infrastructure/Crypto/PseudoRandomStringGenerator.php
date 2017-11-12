<?php

namespace OpenCFP\Infrastructure\Crypto;

use OpenCFP\Domain\Services\RandomStringGenerator;

class PseudoRandomStringGenerator implements RandomStringGenerator
{
    public function generate($length = 40)
    {
        return substr(bin2hex(random_bytes($length)), 0, $length);
    }
}
