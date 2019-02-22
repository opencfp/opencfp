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

namespace OpenCFP\Test\Integration\Domain\Model;

use OpenCFP\Domain\EntityNotFoundException;
use OpenCFP\Domain\Model\Airport;
use OpenCFP\Test\Integration\WebTestCase;

final class AirportTest extends WebTestCase
{
    /**
     * @var Airport
     */
    private $airports;

    protected function setUp()
    {
        parent::setUp();

        $this->airports = new Airport();
    }

    /** @test */
    public function it_queries_airports_table_by_code()
    {
        $airport = $this->airports->withCode('AAC');

        $this->assertSame('AAC', $airport->code);
        $this->assertSame('Al Arish', $airport->name);
        $this->assertSame('Egypt', $airport->country);
    }

    /**
     * @test
     */
    public function it_squawks_when_airport_is_not_found()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('not found');

        $this->airports->withCode('foobarbaz');
    }

    /**
     * @test
     */
    public function itIsNotCaseSensitive()
    {
        $airport = $this->airports->withCode('aac');

        $this->assertSame('AAC', $airport->code);
        $this->assertSame('Al Arish', $airport->name);
        $this->assertSame('Egypt', $airport->country);
    }

    /**
     * @test
     */
    public function itThrowsTheCorrectError()
    {
        $this->expectException(EntityNotFoundException::class);
        $this->expectExceptionMessage('not found');

        $this->airports->withCode('foobarbaz');
    }
}
