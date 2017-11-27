<?php

namespace OpenCFP\Test\Unit\Infrastructure\Crypto;

use OpenCFP\Infrastructure\Crypto\PseudoRandomStringGenerator;

/**
 * @covers \OpenCFP\Infrastructure\Crypto\PseudoRandomStringGenerator
 */
class PseudoRandomStringGeneratorTest extends \PHPUnit\Framework\TestCase
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
        $this->assertEquals(10, \strlen($this->sut->generate(10)));
        $this->assertEquals(18, \strlen($this->sut->generate(18)));
        $this->assertEquals(35, \strlen($this->sut->generate(35)));
        $this->assertEquals(40, \strlen($this->sut->generate(40)));
    }
}
