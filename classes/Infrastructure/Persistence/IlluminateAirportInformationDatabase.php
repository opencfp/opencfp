<?php


namespace OpenCFP\Infrastructure\Persistence;

use Illuminate\Database\Capsule\Manager as Capsule;
use OpenCFP\Domain\AirportInfo;
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
     * @return AirportInfo
     * @throws \Exception
     */
    public function withCode($code)
    {
        $airport = $this->capsule->table('airports')
            ->where('code', $code)->first(['code', 'name', 'country']);

        if (!$airport) {
            throw new \Exception("An airport matching '{$code}' was not found.");
        }

        return AirportInfo::fromData($airport);
    }
}
