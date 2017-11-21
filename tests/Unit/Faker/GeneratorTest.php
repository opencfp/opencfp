<?php

namespace OpenCFP\Test\Unit\Faker;

use Faker\Generator;
use OpenCFP\Test\Helper\Faker\GeneratorTrait;

class GeneratorTest extends \PHPUnit\Framework\TestCase
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
