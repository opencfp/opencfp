<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2019 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Domain\Services;

use OpenCFP\Domain\EntityNotFoundException;
use OpenCFP\Domain\Model\Airport;

interface AirportInformationDatabase
{
    /**
     * @param string $code the IATA Airport Code to get information for
     *
     * @see https://en.wikipedia.org/wiki/International_Air_Transport_Association_airport_code
     *
     * @throws EntityNotFoundException
     *
     * @return Airport
     */
    public function withCode($code);
}
