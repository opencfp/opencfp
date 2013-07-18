<?php

namespace OpenCFP\Tests\Config;

use OpenCFP\Config\InvalidParameterException;

class InvalidParameterExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetParameter()
    {
        $e = new InvalidParameterException('foo');

        $this->assertSame('foo', $e->getParameter());
    }
}