<?php

namespace OpenCFP\Test\Domain;

use OpenCFP\Domain\AirportInfo;

class AirportInfoTest extends \PHPUnit\Framework\TestCase
{

    /** @test */
    public function it_has_an_iata_location_identifier_name_and_country()
    {
        $airport = AirportInfo::make('RDU', 'Raleigh/Durham (NC)', 'USA');

        $this->assertEquals('RDU', $airport->code);
        $this->assertEquals('Raleigh/Durham (NC)', $airport->name);
        $this->assertEquals('USA', $airport->country);
    }

    /** @test */
    public function it_can_be_constructed_from_database_row()
    {
        $airport = AirportInfo::fromData([
            'code' => 'RDU',
            'name' => 'Raleigh/Durham (NC)',
            'country' => 'USA',
        ]);

        $this->assertEquals('RDU', $airport->code);
        $this->assertEquals('Raleigh/Durham (NC)', $airport->name);
        $this->assertEquals('USA', $airport->country);
    }
}
