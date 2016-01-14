<?php

namespace OpenCFP\Test\Util;

use Exception;

/**
 * @group wip
 */
class ImmutableTest extends \PHPUnit_Framework_TestCase
{

    /** @test */
    public function it_throws_exception_when_assignment_is_made_after_construction()
    {
        $object = new ImmutableObject('Foo');

        $this->assertEquals('Foo', $object->value);

        $this->setExpectedException(Exception::class, 'ImmutableObject is immutable.');
        $object->value = 'Bar';
    }
}
