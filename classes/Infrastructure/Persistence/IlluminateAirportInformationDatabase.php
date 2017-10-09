<?php


namespace OpenCFP\Infrastructure\Persistence;

use Illuminate\Database\Capsule\Manager as Capsule;
use OpenCFP\Domain\Model\Airport;
use OpenCFP\Domain\Services\AirportInformationDatabase;

class IlluminateAirportInformationDatabase implements AirportInformationDatabase
{

    /**
     * @var Capsule Illuminate database
     */
    private $capsule;

    public function __construct(Capsule $capsule)
    {
        $this->capsule = $capsule;
    }

    /**
     * @param string $code the IATA Airport Code to get information for
     *
     * @return Airport
     * @throws \Exception
     */
    public function withCode($code)
    {
        $airport = new Airport;
        return $airport->withCode($code);
    }
}
