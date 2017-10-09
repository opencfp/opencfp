<?php

namespace OpenCFP\Domain\Services;

use OpenCFP\Domain\Model\Airport;

interface AirportInformationDatabase
{
    /**
     * @param string $code the IATA Airport Code to get information for
     * @see https://en.wikipedia.org/wiki/International_Air_Transport_Association_airport_code
     *
     * @return Airport
     */
    public function withCode($code);
}
