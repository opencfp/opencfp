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
 *
 * @property bool $viewed
 */
class TalkMeta extends Eloquent
{
    protected $table = 'talk_meta';

    public const CREATED_AT = 'created';
    public const UPDATED_AT = null;

    public const DEFAULT_RATING = 0;
    public const DEFAULT_VIEWED = 0;

    protected $attributes = [
        'rating' => self::DEFAULT_RATING,
        'viewed' => self::DEFAULT_VIEWED,
    ];

    public function talk(): BelongsTo
    {
        return $this->belongsTo(Talk::class, 'talk_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    public function setUpdatedAt($value)
    {
        /**
         * This is the dirty way to tell Illuminate that we don't have an updated at field
         * while still having a created_at field.
         */
    }

    public function viewTalk(): self
    {
        if (!$this->viewed) {
            $this->viewed = true;
            $this->save();
        }

        return $this;
    }
}
