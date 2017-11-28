<?php

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Domain\Model;

use OpenCFP\Domain\Services\AirportInformationDatabase;

class Airport extends Eloquent implements AirportInformationDatabase
{
    protected $table   = 'airports';
    public $timestamps = false;

    /**
     * @param string $code the IATA Airport Code to get information for
     *
     * @throws \Exception
     *
     * @return self
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
