<?php

namespace OpenCFP\Domain;

use Exception;
use OpenCFP\Util\Immutable;

class AirportInfo
{
    use Immutable;

    /**
     * @var string IATA Airport Code
     * @see https://en.wikipedia.org/wiki/International_Air_Transport_Association_airport_code
     */
    private $code;

    /**
     * @var string Name of the airport
     */
    private $name;

    /**
     * @var string Rough string representation of country
     */
    private $country;

    private function __construct($code, $name, $country)
    {
        if (empty($code) || empty($name) || empty($country)) {
            throw new Exception('Airport must have code, name and country.');
        }

        $this->code = $code;
        $this->name = $name;
        $this->country = $country;
    }

    public static function make($code, $name, $country)
    {
        return new static($code, $name, $country);
    }

    public static function fromData(array $data)
    {
        return new static(
            $code = isset($data['code']) ? $data['code'] : null,
            $name = isset($data['name']) ? $data['name'] : null,
            $country = isset($data['country']) ? $data['country'] : null
        );
    }
}
