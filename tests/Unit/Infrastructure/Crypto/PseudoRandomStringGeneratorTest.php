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

namespace OpenCFP\Test\Unit\Infrastructure\Crypto;

use OpenCFP\Infrastructure\Crypto\PseudoRandomStringGenerator;

final class PseudoRandomStringGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PseudoRandomStringGenerator
     */
    private $sut;

    protected function setUp()
    {
        $this->sut = new PseudoRandomStringGenerator();
    }

    /** @test */
    public function it_should_generate_a_random_string_of_given_length()
    {
        $this->assertSame(10, \strlen($this->sut->generate(10)));
        $this->assertSame(18, \strlen($this->sut->generate(18)));
        $this->assertSame(35, \strlen($this->sut->generate(35)));
        $this->assertSame(40, \strlen($this->sut->generate(40)));
    }
}
