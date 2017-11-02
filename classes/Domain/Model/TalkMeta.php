<?php

namespace OpenCFP\Domain\Model;

class TalkMeta extends Eloquent
{
    protected $table = 'talk_meta';

    const CREATED_AT = 'created';
    const UPDATED_AT = null;

    const DEFAULT_RATING = 0;
    const DEFAULT_VIEWED = 0;

    protected $attributes = [
        'rating' => self::DEFAULT_RATING,
        'viewed' => self::DEFAULT_VIEWED,
    ];

    public function talk()
    {
        return $this->belongsTo(Talk::class, 'talk_id');
    }

    public function user()
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

    public function viewTalk()
    {
        if (!$this->viewed) {
            $this->viewed = true;
            $this->save();
        }
        return $this;
    }
}
