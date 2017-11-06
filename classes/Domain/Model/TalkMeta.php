<?php

namespace OpenCFP\Domain\Model;

class TalkMeta extends Eloquent
{
    protected $table = 'talk_meta';
    const CREATED_AT = 'created';
    const UPDATED_AT = null;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

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
}
