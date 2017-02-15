<?php

namespace OpenCFP\Test\Infrastructure\Persistence;

use OpenCFP\Infrastructure\Persistence\IlluminateAirportInformationDatabase;
use OpenCFP\Test\DatabaseTestCase;

/**
 * Tests integration with illuminate/database and airports table to implement
 * an AirportInfromationDatabase
 */
class IlluminateAirportInformationDatabaseTest extends DatabaseTestCase
{
    private $airports;

    protected function setUp()
    {
        parent::setUp();

        $this->airports = $this->getAirportInformationDatabase();
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
     * @return IlluminateAirportInformationDatabase
     */
    private function getAirportInformationDatabase()
    {
        return new IlluminateAirportInformationDatabase($this->getCapsule());
    }
}
