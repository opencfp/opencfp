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
        $airportInfoDatabase = new IlluminateAirportInformationDatabase($this->getCapsule());

        $airport = $airportInfoDatabase->withCode('RDU');

        $this->assertEquals('RDU', $airport->code);
        $this->assertEquals('Raleigh/Durham (NC)', $airport->name);
        $this->assertEquals('USA', $airport->country);
    }

}
