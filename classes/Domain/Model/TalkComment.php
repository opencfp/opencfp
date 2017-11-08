<?php

namespace OpenCFP\Domain\Model;

class TalkComment extends Eloquent
{
    protected $table = 'talk_comments';
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function talk()
    {
        return $this->belongsTo(Talk::class);
    }
}
