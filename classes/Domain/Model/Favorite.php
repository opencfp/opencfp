<?php

namespace OpenCFP\Domain\Model;

class Favorite extends Eloquent
{
    protected $table = 'favorites';

    const CREATED_AT = 'created';
    const UPDATED_AT = null;

    public function talk()
    {
        return $this->belongsTo(Talk::class, 'talk_id');
    }

    public function setUpdatedAt($value)
    {
        /**
         * This is the dirty way to tell Illuminate that we don't have an updated at field
         * while still having a created_at field.
         */
    }
}
