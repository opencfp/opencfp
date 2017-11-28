<?php

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Unit\Faker;

use Faker\Generator;
use OpenCFP\Test\Helper\Faker\GeneratorTrait;

/**
 * @covers \OpenCFP\Test\Helper\Faker\GeneratorTrait
 */
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
