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

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TalkComment extends Eloquent
{
    protected $table = 'talk_comments';

    const CREATED_AT = 'created';
    const UPDATED_AT = null;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function talk(): BelongsTo
    {
        return $this->belongsTo(Talk::class);
    }

    public function setUpdatedAt($value)
    {
        /**
         * This is the dirty way to tell Illuminate that we don't have an updated at field
         * while still having a created_at field.
         */
    }
}
