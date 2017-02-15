<?php

namespace OpenCFP\Test\Util;

use Exception;

class ImmutableTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage ImmutableObject is immutable
     */
    public function it_throws_exception_when_assignment_is_made_after_construction()
    {
        $object = new ImmutableObject('Foo');
        $this->assertEquals('Foo', $object->value);
        $object->value = 'Bar';
    }
}
