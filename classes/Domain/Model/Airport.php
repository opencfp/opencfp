<?php

namespace OpenCFP\Domain\Model;

use OpenCFP\Domain\Services\AirportInformationDatabase;

class Airport extends Eloquent implements AirportInformationDatabase
{
    protected $table = 'airports';
    public $timestamps = false;

    /**
     * @param string $code the IATA Airport Code to get information for
     *
     * @return Airport
     * @throws \Exception
     */
    public function withCode($code): self
    {
        $airport = $this->where('code', $code)->first(['code', 'name', 'country']);

        if (!$airport) {
            throw new \Exception("An airport matching '{$code}' was not found.");
        }
        return $airport;
    }
}
