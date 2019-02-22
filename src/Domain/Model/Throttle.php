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

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static self create(array $attributes)
 * @method static self|null find($id, $columns = ['*'])
 */
class Throttle extends Eloquent
{
    protected $table = 'throttle';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function setUpdatedAt($value)
    {
        /**
         * This is the dirty way to tell Illuminate that we don't have an updated_at field
         */
    }

    public function setCreatedAt($value)
    {
        /**
         * This is the dirty way to tell Illuminate that we don't have a created at field
         */
    }
}
