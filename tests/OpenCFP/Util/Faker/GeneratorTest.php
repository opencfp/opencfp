<?php

namespace OpenCFP\Util\Faker;

use Faker\Generator;

class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    use GeneratorTrait;

    public function testGetFakerReturnsFaker()
    {
        $faker = $this->getFaker();

        $this->assertInstanceOf(Generator::class, $faker);
    }

    public function testGetFakerReturnsSameInstance()
    {
        $faker = $this->getFaker();

        $this->assertSame($faker, $this->getFaker());
    }
}
