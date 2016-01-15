<?php


namespace OpenCFP\Infrastructure\Persistence;


use OpenCFP\Domain\AirportInfo;
use OpenCFP\Domain\Services\AirportInformationDatabase;
use Illuminate\Database\Capsule\Manager as Capsule;

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
     * @see https://en.wikipedia.org/wiki/International_Air_Transport_Association_airport_code
     *
     * @return AirportInfo
     */
    public function withCode($code)
    {
        $airport = $this->capsule->table('airports')
            ->where('code', $code)->first(['code', 'name', 'country']);

        return AirportInfo::fromData($airport);
    }
}