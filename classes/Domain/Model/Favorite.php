<?php

namespace OpenCFP\Domain\Model;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    protected $table = 'favorites';
    public $timestamps = false;

    public function talk()
    {
        $this->belongsTo(Talk::class, 'talk_id');
    }
}
