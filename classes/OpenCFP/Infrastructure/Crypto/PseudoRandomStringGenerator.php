<?php

namespace OpenCFP\Infrastructure\Crypto;

use OpenCFP\Domain\Services\RandomStringGenerator;
use RandomLib\Factory;

class PseudoRandomStringGenerator implements RandomStringGenerator
{
    /**
     * @var Generator
     */
    private $generator;

    public function __construct(Factory $factory)
    {
        $this->generator = $factory->getMediumStrengthGenerator();
    }

    public function generate($length = 40)
    {
        return $this->generator->generateString($length, $this->getCharacterSet());
    }

    private function getCharacterSet()
    {
        return '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    }
}