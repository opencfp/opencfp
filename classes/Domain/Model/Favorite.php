<?php

namespace OpenCFP\Domain\Model;

class Favorite extends Eloquent
{
    protected $table = 'favorites';
    public $timestamps = false;

    public function talk()
    {
        return $this->belongsTo(Talk::class, 'talk_id');
    }
}
