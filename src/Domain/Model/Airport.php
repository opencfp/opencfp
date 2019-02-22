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

namespace OpenCFP\Domain\Model;

use OpenCFP\Domain\EntityNotFoundException;
use OpenCFP\Domain\Services\AirportInformationDatabase;

/**
 * @method static self create(array $attributes)
 * @method static self|null find($id, $columns = ['*'])
 */
class Airport extends Eloquent implements AirportInformationDatabase
{
    protected $table = 'airports';

    public $timestamps = false;

    public function withCode($code): self
    {
        $airport = $this->where('code', $code)->first(['code', 'name', 'country']);

        if (!$airport instanceof self) {
            throw new EntityNotFoundException("An airport matching '{$code}' was not found.");
        }

        return $airport;
    }
}
