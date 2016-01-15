<?php

namespace OpenCFP\Test\Infrastructure\Persistence;

use Illuminate\Database\Capsule\Manager as Capsule;
use OpenCFP\Infrastructure\Persistence\IlluminateAirportInformationDatabase;

/**
 * Tests integration with illuminate/database and airports table to implement
 * an AirportInfromationDatabase
 *
 * @group wip
 */
class IlluminateAirportInformationDatabaseTest extends \DatabaseTestCase
{

    /** @test */
    public function it_queries_airports_table_by_code()
    {
        $airports = $this->getAirportInformationDatabase();

        $airport = $airports->withCode('RDU');

        $this->assertEquals('RDU', $airport->code);
        $this->assertEquals('Raleigh/Durham (NC)', $airport->name);
        $this->assertEquals('USA', $airport->country);
    }

    /** @test */
    public function it_squawks_when_airport_is_not_found()
    {
        $this->setExpectedException(\Exception::class, 'not found');

        $airports = $this->getAirportInformationDatabase();
        $airport = $airports->withCode('foobarbaz');
    }

    /**
     * @return IlluminateAirportInformationDatabase
     */
    private function getAirportInformationDatabase()
    {
        return new IlluminateAirportInformationDatabase($this->getCapsule());
    }

}
