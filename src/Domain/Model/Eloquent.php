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

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static int count()
 * @method static Builder orderBy(string $column, string $direction = 'asc')
 * @method static Builder where(string $column, $value = null)
 */
class Eloquent extends Model
{
    protected $guarded = [];
}
