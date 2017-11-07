<?php

namespace OpenCFP\Test\Domain\Model;

use OpenCFP\Domain\Model\Airport;
use OpenCFP\Test\DatabaseTransaction;
use OpenCFP\Test\TestCase;

/**
 * @group db
 */
class AirportTest extends TestCase
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
     * @expectedException \Exception
     * @expectedExceptionMessage not found
     */
    public function it_squawks_when_airport_is_not_found()
    {
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
