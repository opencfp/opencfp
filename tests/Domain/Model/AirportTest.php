<?php

namespace OpenCFP\Test\Domain\Model;

use OpenCFP\Domain\Model\Airport;
use OpenCFP\Test\DatabaseTransaction;

/**
 * @group db
 */
class AirportTest extends \PHPUnit\Framework\TestCase
{
    use DatabaseTransaction;

    private $airports;

    public function setUp()
    {
        $this->setUpDatabase();
        $this->airports = $this->getAirport();
    }

    public function tearDown()
    {
        $this->tearDownDatabase();
    }

    /** @test */
    public function it_queries_airports_table_by_code()
    {
        $airport = $this->airports->withCode('RDU');

        $this->assertEquals('RDU', $airport->code);
        $this->assertEquals('Raleigh/Durham (NC)', $airport->name);
        $this->assertEquals('USA', $airport->country);
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
     * @return Airport
     */
    private function getAirport()
    {
        return new Airport;
    }
}
