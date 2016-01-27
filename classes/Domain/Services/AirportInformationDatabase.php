<?php

namespace OpenCFP\Domain\Services;

use OpenCFP\Domain\AirportInfo;

interface AirportInformationDatabase
{
    /**
     * @param string $code the IATA Airport Code to get information for
     * @see https://en.wikipedia.org/wiki/International_Air_Transport_Association_airport_code
     *
     * @return AirportInfo
     */
    public function withCode($code);
}
